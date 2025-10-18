<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f2f2f2;
        }

        .container {
            background: #ffffff;
            padding: 2rem 2.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        button {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
        }

        .back-link {
            display: block;
            margin-top: 1rem;
            text-align: center;
        }

        .back-link a {
            text-decoration: none;
            font-size: 0.95rem;
            color: #0077cc;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Forgot Password</h1>
        <form method="POST" action="send-password-reset.php">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <button type="submit">Send Reset Link</button>
        </form>
        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>

</body>
</html>
