<?php include "../config.php"; include "../includes/header.php"; ?>

<div class="container-box text-center">

    <h1 class="page-title">Smart Queue System</h1>
    
    <p class="lead">Fast • Fair • Transparent Service</p>

    <div class="hero-banner card-inner">
        <h3 style="color: #0d6efd; margin-bottom: 15px;">How It Works</h3>
        <ol class="how-it">
            <li>Select your service</li>
            <li>Get your ticket number</li>
            <li>Wait for your turn</li>
            <li>Check display screen anytime</li>
        </ol>
    </div>

    <div class="cta-group">
            <a href="join_queue.php" class="btn btn-primary btn-main">
                Get Ticket Now
            </a>
            <a href="display.php" class="btn btn-outline-primary btn-main" target="_blank">
                View Queue Status
            </a>
            <a href="status.php" class="btn btn-outline-success btn-main" style="margin-top:10px;">
                Check Ticket Status
            </a>
    </div>

</div>

<?php include "../includes/footer.php"; ?>
