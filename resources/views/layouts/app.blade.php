<!DOCTYPE html>
<html>

<head>
    <title>10bob WiFi</title>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">

<div class="container">

<a class="navbar-brand" href="/">
10bob WiFi
</a>

<button class="navbar-toggler"
data-bs-toggle="collapse"
data-bs-target="#menu">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse" id="menu">

<ul class="navbar-nav ms-auto">

<li class="nav-item">
<a class="nav-link" href="/">
Home
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/buy">
Buy Internet
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/voucher">
Voucher
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/admin/dashboard">
Dashboard
</a>
</li>

</ul>

</div>

</div>

</nav>

<div class="container mt-5">
    @yield('content')
</div>

</body>

</html>