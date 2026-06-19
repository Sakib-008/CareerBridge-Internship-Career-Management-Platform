<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 — Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="text-center">
        <h1 class="display-1 fw-bold text-danger">403</h1>
        <h4 class="mb-3">Access Denied</h4>
        <p class="text-muted">{{ $exception->getMessage() ?? 'You do not have permission to view this page.' }}</p>
        <a href="{{ url()->previous() }}" class="btn btn-outline-dark">
            Go Back
        </a>
    </div>
</body>
</html>