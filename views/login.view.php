<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($companyName); ?></title>
    <link rel="stylesheet" href="<?php echo $baseURL; ?>assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    
    <!-- Immediate theme check to prevent flash -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modern background blobs for glassmorphism aesthetics */
        .background-blobs {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            overflow: hidden;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        [data-theme="dark"] .background-blobs {
            background: linear-gradient(135deg, #0f172a 0%, #020617 100%);
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float-blob 20s infinite alternate ease-in-out;
        }
        .blob-1 {
            width: 300px;
            height: 300px;
            background: #4f46e5;
            top: 10%;
            left: 15%;
        }
        .blob-2 {
            width: 400px;
            height: 400px;
            background: #10b981;
            bottom: 10%;
            right: 15%;
            animation-delay: -5s;
        }
        @keyframes float-blob {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 30px) scale(1.1); }
        }
    </style>
</head>
<body class="login-wrapper">
    
    <div class="background-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="glass-panel login-card">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 32px; color: var(--accent); margin-bottom: 8px;">
                <i class="fa-solid fa-rocket-launch"></i>
            </div>
            <h2 style="font-weight: 700; letter-spacing: -0.5px; font-size: 24px;"><?php echo htmlspecialchars($companyName); ?></h2>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: 6px;">Sign in to access your business control panel</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: var(--danger-light); color: var(--danger); padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.1);">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <input type="hidden" name="csrf_token" value="<?php echo \App\Middleware\CSRFMiddleware::generateToken(); ?>">
            
            <div class="form-group">
                <label for="email">Work Email</label>
                <div style="position: relative;">
                    <i class="fa-regular fa-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="name@company.com" style="padding-left: 45px;" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" style="padding-left: 45px;" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 14px; border-radius: 12px; font-size: 15px;">
                Sign In <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>

</body>
</html>
