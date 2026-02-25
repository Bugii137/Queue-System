<?php
include "../config.php";

// fetch active services
$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1");
$services = $stmt->fetchAll();

// preselected service id from query string (optional)
$preselect_service = isset($_GET['service_id']) ? intval($_GET['service_id']) : null;

// fetch selected service details if preselected
$selected_service = null;
if ($preselect_service) {
    $sstmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $sstmt->execute([$preselect_service]);
    $selected_service = $sstmt->fetch();
}
?>

<?php include "../includes/header.php"; ?>

<div class="container-box">

    <h1 class="page-title">Join Queue</h1>

    <?php if ($selected_service): ?>
        <div class="selected-service">
            <strong style="font-size:16px;">Selected Service:</strong>
            <div class="name"><?= htmlspecialchars($selected_service['service_name']); ?></div>
            <div class="meta">Average time: <?= intval($selected_service['average_time_minutes']); ?> minutes</div>
        </div>
    <?php endif; ?>

    <form method="POST" action="generate_ticket.php">

            <!-- Name -->
            <div class="form-group">
                <label class="form-label">Your Name</label>
                <input type="text" name="customer_name"
                       class="form-control"
                       placeholder="Enter your full name"
                       required>
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label class="form-label">Phone Number (Optional)</label>
                <input type="text" name="customer_phone"
                       class="form-control"
                       placeholder="e.g. 0712345678">
            </div>

                <!-- Appointment Date -->
                <div class="form-group">
                    <label class="form-label">Appointment Date</label>
                    <input type="date" name="appointment_date" class="form-control" min="<?= date('Y-m-d'); ?>" required>
                </div>

                <!-- Appointment Time -->
                <div class="form-group">
                    <label class="form-label">Appointment Time</label>
                    <input type="time" name="appointment_time" class="form-control" required>
                </div>

            <!-- Service -->
            <div class="form-group">
                <label class="form-label">Select Service</label>

                <select name="service_id"
                        class="form-select"
                        required>

                    <option value="">-- Choose a service --</option>

                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['id']; ?>" <?= ($preselect_service && $preselect_service == $service['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($service['service_name']); ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="cta-group">
                <button type="submit" class="btn btn-primary btn-main">Get Ticket</button>
                <a href="index.php" class="btn btn-outline-secondary btn-main">Back</a>
            </div>

        </form>

</div>

<?php include "../includes/footer.php"; ?>
