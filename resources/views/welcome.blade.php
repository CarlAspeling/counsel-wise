<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Counselwise</title>
</head>
<body>
<div>
    <h1>Welcome to Counselwise</h1>
    <p>A platform for HPCSA registered counsellors providing novel therapeutic recommendations.</p>
    
    @auth
        <a href="{{ route('dashboard') }}">Dashboard</a>
    @else
        <a href="{{ route('login') }}">Login</a>
        <a href="{{ route('register') }}">Sign Up</a>
    @endauth
</div>
</body>
</html>
