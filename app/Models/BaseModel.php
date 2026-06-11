<?php

namespace App\Models;

use App\Events\LoadDataEvent;
use App\Models\SSO\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Str;

trait BaseModel
{
  use LogsActivity;

  protected static function booted()
  {
    static::creating(function ($model) {
      if ($model->keyType === 'string' && !$model->getKey()) {
        $model->{$model->getKeyName()} = (string) Str::uuid();
      }
      if (Auth::check() && $model->fillable && in_array('created_by', $model->fillable) && in_array('updated_by', $model->fillable)) {
        $model->created_by = Auth::user()->username;
        $model->updated_by = Auth::user()->username;
      }
      event(new LoadDataEvent([
        'action'  => 'load-data',
        'table'   => $model->getTable(),
      ]));
    });
    static::updating(function ($model) {
      if (Auth::check() && $model->fillable && in_array('updated_by', $model->fillable)) {
        $model->updated_by = Auth::user()->username;
      }
      event(new LoadDataEvent([
        'action'  => 'load-data',
        'table'   => $model->getTable(),
      ]));
    });
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logAll()
      ->useLogName($this->connection)
      ->setDescriptionForEvent(fn(string $eventName) => "{$this->table} has been $eventName")
      ->logOnlyDirty();
  }

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by', 'username');
  }

  public function updater()
  {
    return $this->belongsTo(User::class, 'updated_by', 'username');
  }

  public function deleter()
  {
    return $this->belongsTo(User::class, 'deleted_by', 'username');
  }

  public function scopeInclude($query)
  {
    if (request()->has('include') && is_array(request('include')) && count(request('include')) > 0) {
      $relationWithFields = $this->buildAllRelationsWithFieldSelection();

      // Debug: Log untuk melihat relasi yang akan di-load
      if (config('app.debug')) {
        Log::info('Relations with field selection:', $relationWithFields);
      }

      if (!empty($relationWithFields)) {
        return $query->with($relationWithFields);
      } else {
        // Fallback ke include normal jika tidak ada field selection
        return $query->with(request('include'));
      }
    }
  }

  public function scopeSelectColumns($query)
  {
    if (request()->has('fields') && is_array(request('fields')) && count(request('fields')) > 0) {
      return $query->select(request('fields'));
    }
    return $query;
  }

  public function scopeFilter($query)
  {
    $this->applySearchFilter($query);
    $this->applyColumnFilters($query);
    $this->applyWheres($query);
  }

  public function scopeJoins($query)
  {
    if (request()->has('joins') && is_array(request('joins')) && count(request('joins')) > 0) {
      foreach (request('joins') as $join) {
        $parts = explode(',', $join);
        $table = $parts[0];
        $on1 = $parts[1] ?? null;
        $condition = $parts[2] ?? null;
        $on2 = $parts[3] ?? null;
        $type = $parts[4] ?? 'inner';

        if ($table && $on1 && $condition && $on2) {
          switch (strtolower($type)) {
            case 'left':
              $query->leftJoin($table, $on1, $condition, $on2);
              break;
            case 'right':
              $query->rightJoin($table, $on1, $condition, $on2);
              break;
            case 'cross':
              $query->crossJoin($table, $on1, $condition, $on2);
              break;
            default:
              $query->join($table, $on1, $condition, $on2);
              break;
          }
        }
      }
    }
  }

  private function applySearchFilter($query)
  {
    if (!request()->has('q')) {
      return;
    }

    $search = request('q');
    $table = $this->table;

    $query->where(function ($subQuery) use ($table, $search) {
      if (request()->has('fields') && is_array(request('fields')) && count(request('fields')) > 0) {
        foreach (request('fields') as $field) {
          if (str_contains($field, '.*')) {
            $field = str_replace('.*', '', $field);
            $columns = DB::connection($this->getConnectionName())->getSchemaBuilder()->getColumnListing($field);
            foreach ($columns as $column) {
              $subQuery->orWhere("{$field}.{$column}", 'like', '%' . $search . '%');
            }
          } else {
            if (str_contains($field, ' as ')) {
              $field = explode(' as ', $field)[0];
            }
            $subQuery->orWhere("{$field}", 'like', '%' . $search . '%');
          }
        }
      } else {
        foreach ($this->fillable as $key => $column) {
          $method = $key === 0 ? 'where' : 'orWhere';
          $subQuery->$method("{$table}.{$column}", 'like', '%' . $search . '%');
        }
      }

      if (request()->has('include') && is_array(request('include')) && count(request('include')) > 0) {
        foreach (request('include') as $relation) {
          $this->applySearchNestedRelation($subQuery, $relation, $search);
        }
      }
    });
  }

  private function applySearchNestedRelation($query, $relation, $search)
  {
    // Pisahkan antara path dan field
    $parts = explode(':', $relation);
    $path = $parts[0]; // e.g. "course.department.faculty"
    $fields = isset($parts[1]) ? array_filter(array_map('trim', explode(',', $parts[1]))) : [];

    $relations = explode('.', $path);

    $this->recursiveRelationSearch($query, $relations, $fields, $search);
  }

  public function recursiveRelationSearch($query, $relations, $fields, $search)
  {
    $this->recursiveRelationSearchOnModel($this, $query, $relations, $fields, $search, true);
  }

  private function recursiveRelationSearchOnModel($model, $query, $relations, $fields, $search, $useOr = false)
  {
    $relationName = array_shift($relations);

    if (!$relationName || !method_exists($model, $relationName)) {
      return;
    }

    $whereHasMethod = $useOr ? 'orWhereHas' : 'whereHas';

    $query->$whereHasMethod($relationName, function ($relQuery) use ($model, $relations, $fields, $search, $relationName) {
      $relatedModel = $model->$relationName()->getRelated();
      $table = $relatedModel->getTable();
      try {
        $schemaTable = $this->extractSchemaTableName($table);
        $columns = DB::connection($relatedModel->getConnectionName())->getSchemaBuilder()->getColumnListing($schemaTable);
      } catch (\Exception $e) {
        // If unable to get columns, fallback to all fields
        $columns = [];
      }

      $searchFields = $this->resolveRelationSearchFields($fields, $columns, $relatedModel->getKeyName());

      if (!empty($relations)) {
        $this->recursiveRelationSearchOnModel($relatedModel, $relQuery, $relations, $fields, $search, false);
      } else {
        // Sudah mentok, apply field search
        foreach ($searchFields as $i => $field) {
          $method = $i === 0 ? 'where' : 'orWhere';
          $relQuery->$method($field, 'like', '%' . $search . '%');
        }
      }
    });
  }

  private function extractSchemaTableName($table)
  {
    // Convert `database.schema.table` to `schema.table` for schema builder lookup.
    $parts = explode('.', str_replace(['[', ']'], '', (string) $table));

    if (count($parts) >= 2) {
      return implode('.', array_slice($parts, -2));
    }

    return $table;
  }

  private function resolveRelationSearchFields($fields, $columns, $fallbackKey)
  {
    $normalizedFields = array_values(array_filter(array_map(function ($field) {
      return $this->normalizeSearchField($field);
    }, (array) $fields)));

    if (!empty($normalizedFields)) {
      if (empty($columns)) {
        return array_values(array_unique($normalizedFields));
      }

      $validFields = array_values(array_intersect($normalizedFields, $columns));
      return !empty($validFields) ? $validFields : array_values(array_unique($normalizedFields));
    }

    return [$fallbackKey];
  }

  private function normalizeSearchField($field)
  {
    $field = trim((string) $field);
    if ($field === '' || $field === '*') {
      return null;
    }

    if (str_contains(strtolower($field), ' as ')) {
      $field = preg_split('/\s+as\s+/i', $field)[0] ?? $field;
    }

    $field = str_replace(['[', ']'], '', $field);
    if (str_contains($field, '.')) {
      $parts = explode('.', $field);
      $field = end($parts);
    }

    return trim($field) ?: null;
  }

  private function applyColumnFilters($query)
  {
    $filters = request()->all();
    $filterLogic = strtolower((string) request('filter_logic', 'and'));
    $useOrLogic = in_array($filterLogic, ['or', 'any', 'one'], true);

    // Parse or_filters parameter: "lecturer_members->lecturer_id,reviews->reviewer_id"
    $orFiltersParam = request('or_filters', '');
    $orFiltersList = !empty($orFiltersParam) ? array_map('trim', explode(',', $orFiltersParam)) : [];

    $query->where(function ($subQuery) use ($filters, $useOrLogic, $orFiltersList) {
      $andFilters = [];
      $orGroupFilters = [];

      // Pisahkan filter ke AND dan OR group
      foreach ($filters as $column => $value) {
        // Skip parameter non-filter
        if (in_array($column, [
          'q',
          'include',
          'fields',
          'joins',
          'wheres',
          'details',
          'sort_by',
          'sort_type',
          'per_page',
          'page',
          'filter_logic',
          'or_filters',
        ])) {
          continue;
        }

        $originalColumn = $column;
        if (str_contains($column, ':')) {
          $parts = explode(':', $column);
          $originalColumn = $parts[0];
        }

        // Cek apakah column ini ada di or_filters list
        if (in_array($originalColumn, $orFiltersList)) {
          $orGroupFilters[$column] = $value;
        } else {
          $andFilters[$column] = $value;
        }
      }

      // Apply AND filters terlebih dahulu
      $filterIndex = 0;
      foreach ($andFilters as $column => $value) {
        $operator = '=';
        if (str_contains($column, ':')) {
          $parts = explode(':', $column);
          $operator = end($parts);
          $column = $parts[0];
        }

        $this->applyColumnFilter($subQuery, $column, $operator, $value, 'and');
        $filterIndex++;
      }

      // Apply OR group filters - wrap dalam where() agar menjadi (A OR B)
      if (!empty($orGroupFilters)) {
        $subQuery->where(function ($orGroup) use ($orGroupFilters) {
          $orIndex = 0;
          foreach ($orGroupFilters as $column => $value) {
            $operator = '=';
            if (str_contains($column, ':')) {
              $parts = explode(':', $column);
              $operator = end($parts);
              $column = $parts[0];
            }

            $boolean = $orIndex === 0 ? 'and' : 'or';
            $this->applyColumnFilter($orGroup, $column, $operator, $value, $boolean);
            $orIndex++;
          }
        });
      }
    });
  }

  private function applyColumnFilter($subQuery, $column, $operator, $value, $boolean = 'and')
  {
    // Jika mengandung relasi (misal "profile-address-city")
    if (str_contains($column, '->')) {
      $parts = explode('->', $column);
      $relation = implode('->', array_slice($parts, 0, -1)); // "profile-address"
      $column = end($parts); // "city"

      $relationMethod = $boolean === 'or' ? 'orWhereHas' : 'whereHas';
      $subQuery->$relationMethod(str_replace('->', '.', $relation), function ($relQuery) use ($column, $operator, $value) {
        $this->wheres($relQuery, $column, $operator, $value, 'and');
      });
    } else {
      $this->wheres($subQuery, $column, $operator, $value, $boolean);
    }
  }

  private function applyWheres($query)
  {
    $query->when(request()->has('wheres') && is_array(request('wheres')), function ($q) {
      $q->where(function ($q) {
        foreach (request('wheres') as $where) {
          $parts = explode(',', $where);
          $column = $parts[0];
          $operator = $parts[1] ?? '=';
          $value = $parts[2] ?? null;
          $this->wheres($q, $column, $operator, $value);
        }
      });
    });
  }

  private function wheres($q, $column, $operator, $value, $boolean = 'and')
  {
    if ($column && $value !== null) {
      switch ($operator) {
        case 'in':
          if ($boolean === 'or') {
            $q->orWhereIn($column, explode('|', $value));
          } else {
            $q->whereIn($column, explode('|', $value));
          }
          break;
        case 'not_in':
          if ($boolean === 'or') {
            $q->orWhereNotIn($column, explode('|', $value));
          } else {
            $q->whereNotIn($column, explode('|', $value));
          }
          break;

        case 'null':
          if ($boolean === 'or') {
            $q->orWhereNull($column);
          } else {
            $q->whereNull($column);
          }
          break;
        case 'not_null':
          if ($boolean === 'or') {
            $q->orWhereNotNull($column);
          } else {
            $q->whereNotNull($column);
          }
          break;

        case 'not_like':
          if ($boolean === 'or') {
            $q->orWhere($column, 'not like', $value);
          } else {
            $q->where($column, 'not like', $value);
          }
          break;

        case 'like':
        case '!=':
        case '<>':
        case '>':
        case '>=':
        case '<':
        case '<=':
          if ($boolean === 'or') {
            $q->orWhere($column, $operator, $value);
          } else {
            $q->where($column, $operator, $value);
          }
          break;

        case 'between':
          if ($boolean === 'or') {
            $q->orWhereBetween($column, explode('|', $value));
          } else {
            $q->whereBetween($column, explode('|', $value));
          }
          if ($value && str_contains($value, '|')) {
              [$start, $end] = explode('|', $value);
              if ($boolean === 'or') {
                $q->orWhereBetween($column, [$start, $end]);
              } else {
                $q->whereBetween($column, [$start, $end]);
              }
          }
          break;

        case 'year':
          if ($boolean === 'or') {
            $q->orWhereYear($column, $value);
          } else {
            $q->whereYear($column, $value);
          }
          break;
        case 'month':
          if ($boolean === 'or') {
            $q->orWhereMonth($column, $value);
          } else {
            $q->whereMonth($column, $value);
          }
          break;
        case 'day':
          if ($boolean === 'or') {
            $q->orWhereDay($column, $value);
          } else {
            $q->whereDay($column, $value);
          }
          break;
        case 'date':
          if ($boolean === 'or') {
            $q->orWhereDate($column, $value);
          } else {
            $q->whereDate($column, $value);
          }
          break;

        default:
          if ($boolean === 'or') {
            $q->orWhere($column, $value);
          } else {
            $q->where($column, $value);
          }
          break;
      }
    }
  }

  public function scopeList($query, $options = [])
  {
    $defaults = [
      'sort_by'   => 'created_at',
      'sort_type' => 'desc',
      'per_page'  => 10,
      'page'      => 1,
    ];

    $options = array_merge($defaults, $options);
    $query->include()->filter()->joins()->selectColumns();

    $sortBy = request('sort_by', $options['sort_by']);
    $sortType = request('sort_type', $options['sort_type']);

    // cek apakah sort_by mengandung relasi
    if (str_contains($sortBy, '->')) {
      // $sortBy = "education_history->study_program->nama_english"
      $parts = explode('->', $sortBy);
      $column = array_pop($parts);
      $relationPath = implode('.', $parts);
      $alias = str_replace('->', '_', $sortBy);

      // Gunakan approach yang lebih robust untuk nested relations
      try {
        // Coba withAggregate untuk relasi sederhana
        if (count($parts) === 1) {
          $query->withAggregate($relationPath, $column);
          $query->orderBy($alias, $sortType);
        } else {
          // Untuk nested relations yang lebih dalam, gunakan subquery
          $this->applySortByNestedRelation($query, $parts, $column, $sortType, $alias);
        }
      } catch (\Exception $e) {
        // Fallback ke sorting biasa jika ada error
        $query->orderBy('created_at', $sortType);
      }
    } else {
      // default orderBy field di tabel utama
      $query->orderBy($sortBy, $sortType);
    }

    Log::info('Final SQL Query for List:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

    $paginated = $query->paginate(
      request('per_page', $options['per_page']),
      ['*'],
      'page',
      request('page', $options['page'])
    );

    $response = $paginated->toArray();
    $response['success'] = true;
    return $response;
  }

  private function applySortByNestedRelation($query, $relations, $column, $sortType, $alias)
  {
    // Untuk nested relations yang dalam, gunakan approach berbeda
    // Karena withAggregate tidak support nested relations dengan baik

    try {
      // Untuk sorting yang actual, kita perlu join manual (hanya untuk sorting)
      $this->applyNestedJoinForSorting($query, $relations, $column, $sortType);

    } catch (\Exception $e) {
      // Jika gagal, fallback ke created_at
      $query->orderBy('created_at', $sortType);
    }
  }

  private function buildAllRelationsWithFieldSelection()
  {
    $relationWithFields = [];
    $includeFields = $this->parseIncludeFieldSelection();

    foreach ($includeFields as $relationPath => $fields) {
      if ($fields === ['*']) {
        // Jika tidak ada field specification, gunakan default include
        $relationWithFields[$relationPath] = function($query) {};
      } else {
        $relationWithFields[$relationPath] = function($query) use ($fields) {
          // Hanya select field yang dispesifikasi + primary key
          $model = $query->getModel();
          $finalFields = $fields;

          $primaryKey = $model->getKeyName();
          if (!in_array($primaryKey, $finalFields)) {
            $finalFields[] = $primaryKey;
          }

          $query->select($finalFields);
        };
      }
    }

    return $relationWithFields;
  }

  private function addRequiredKeysForRelation($fields, $relationPath)
  {
    $finalFields = array_merge([], $fields); // Copy array

    // Parse relation path untuk mendapatkan model dan relation details
    $relations = explode('.', $relationPath);
    $currentModel = $this;

    // Untuk final relation di path ini
    foreach ($relations as $relationName) {
      if (method_exists($currentModel, $relationName)) {
        $relationInstance = $currentModel->$relationName();
        $relatedModel = $relationInstance->getRelated();

        // Tambahkan primary key jika belum ada
        $primaryKey = $relatedModel->getKeyName();
        if (!in_array($primaryKey, $finalFields)) {
          $finalFields[] = $primaryKey;
        }

        // Tambahkan foreign key untuk relationship ini jika belum ada
        if ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
          $foreignKey = $relationInstance->getOwnerKeyName();
          if (!in_array($foreignKey, $finalFields)) {
            $finalFields[] = $foreignKey;
          }
        } else {
          // HasMany, HasOne, dll
          $foreignKey = $relationInstance->getForeignKeyName();
          if (!in_array($foreignKey, $finalFields)) {
            $finalFields[] = $foreignKey;
          }
        }

        $currentModel = $relatedModel;
      }
    }

    return array_unique($finalFields);
  }

  private function applyNestedJoinForSorting($query, $relations, $column, $sortType)
  {
    $currentModel = $this;
    $finalTable = null;
    $previousTableAlias = null;

    // Build chain of joins - hanya untuk sorting, tidak perlu field selection
    foreach ($relations as $index => $relationName) {
      if (method_exists($currentModel, $relationName)) {
        $relationInstance = $currentModel->$relationName();
        $relatedModel = $relationInstance->getRelated();

        $currentTable = $currentModel->getTable();
        $relatedTable = $relatedModel->getTable();

        // Buat alias untuk table supaya tidak conflict
        $tableAlias = $relatedTable . '_sort_' . $index;

        if ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
          $localKey = $relationInstance->getForeignKeyName();
          $foreignKey = $relationInstance->getOwnerKeyName();
        } else {
          $localKey = $relationInstance->getLocalKeyName();
          $foreignKey = $relationInstance->getForeignKeyName();
        }

        // Tentukan table alias sebelumnya
        $fromTable = $index === 0 ? $currentTable : $previousTableAlias;

        $query->leftJoin(
          "{$relatedTable} as {$tableAlias}",
          "{$fromTable}.{$localKey}",
          '=',
          "{$tableAlias}.{$foreignKey}"
        );

        $finalTable = $tableAlias;
        $previousTableAlias = $tableAlias; // Simpan alias saat ini untuk iterasi berikutnya
        $currentModel = $relatedModel;
      }
    }

    // Apply sorting menggunakan final table
    if ($finalTable) {
      $query->orderBy("{$finalTable}.{$column}", $sortType);
    }
  }

  private function parseIncludeFieldSelection()
  {
    $includes = [];
    if (request()->has('include') && is_array(request('include'))) {
      foreach (request('include') as $include) {
        if (str_contains($include, ':')) {
          [$path, $fields] = explode(':', $include, 2);
          $fieldArray = array_map('trim', explode(',', $fields));
          $includes[$path] = $fieldArray;
        } else {
          $includes[$include] = ['*']; // Select semua jika tidak ada field specification
        }
      }
    }
    return $includes;
  }

  private function getFieldsForRelation($includeFields, $relationPath)
  {
    // Cari field selection untuk relasi path ini
    if (isset($includeFields[$relationPath])) {
      return $includeFields[$relationPath];
    }

    // Cari parent path yang paling mendekati
    $pathParts = explode('.', $relationPath);
    while (count($pathParts) > 1) {
      array_pop($pathParts);
      $parentPath = implode('.', $pathParts);
      if (isset($includeFields[$parentPath])) {
        return $includeFields[$parentPath];
      }
    }

    // Default fallback - minimal fields
    return ['id'];
  }
}
