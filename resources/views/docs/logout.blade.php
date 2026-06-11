<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Redirecting ...</title>
    <style>
        html {
            font-family: Helvetica, sans-serif;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            width: 100%;
            margin: 0;
        }

        .loader {
            width: 350px;
            border-radius: 10px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-evenly;
            padding: 30px;
            box-shadow: 2px 2px 10px -5px lightgrey;
        }

        .loading {
            width: 100%;
            height: 10px;
            background: lightgrey;
            border-radius: 10px;
            position: relative;
        }

        .loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 10px;
            background: #002;
            border-radius: 10px;
            z-index: 1;
            animation: loading 0.6s alternate infinite;
        }

        label {
            color: #002;
            font-size: 18px;
            animation: bit 0.6s alternate infinite;
        }

        @keyframes bit {
            from {
                opacity: 0.3;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes loading {
            0% {
                left: 25%;
            }

            100% {
                left: 50%;
            }

            0% {
                left: 0%;
            }
        }
    </style>
</head>

<body onload="loading()">
    <noscript>
        <p><strong>Note:</strong> Since your browser does not support JavaScript, you must press the button below once
            to proceed.</p>
    </noscript>

    <div class="loader">
        <img src="{{ asset('logo/Loading-UIII.gif') }}" alt="Logo"
            style="width: 100%; height: 100px; object-fit:cover;" />
        <label>Redirecting...</label>
        <div class="loading"></div>
    </div>

    <script>
        function loading() {
            localStorage.removeItem("token");
            window.location.href = "{{ env('VITE_SSO_URL') }}/saml/logout";
        }
    </script>
</body>

</html>
