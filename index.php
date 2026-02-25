<?php include "config.php"; ?>
<?php include "includes/header.php"; ?>

<div style="min-height: calc(100vh - 120px); display: flex; align-items: center; justify-content: center;">

    <div style="max-width: 800px; width: 100%; padding: 20px;">

        <!-- HEADER SECTION -->
        <div style="text-align: center; margin-bottom: 50px;">
            <h1 class="page-title" style="font-size: 42px; margin-bottom: 15px;">
                Smart Queue System
            </h1>
            <p style="font-size: 18px; color: #666; margin: 0;">
                Fast, Fair & Transparent Service Management
            </p>
        </div>

        <!-- ACTION CARDS -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">

            <!-- CUSTOMER CARD -->
            <div style="background: white; border-radius: 14px; padding: 35px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); text-align: center; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 40px rgba(0,0,0,0.12)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)';">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; margin: 0 auto 20px;">-</div>
                <h3 style="color: #1a3a52; margin-bottom: 12px; font-weight: 700;">Get Ticket</h3>
                <p style="color: #666; margin-bottom: 25px; font-size: 14px; line-height: 1.6;">
                    Join the queue quickly and get your ticket number. Track your position in real-time.
                </p>
                <a href="customer/index.php" class="btn btn-primary btn-main" style="background: #0d6efd;">
                    Customer Portal
                </a>
            </div>

            <!-- ADMIN CARD -->
            <div style="background: white; border-radius: 14px; padding: 35px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); text-align: center; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 40px rgba(0,0,0,0.12)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)';">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; margin: 0 auto 20px;">-</div>
                <h3 style="color: #1a3a52; margin-bottom: 12px; font-weight: 700;">Admin Panel</h3>
                <p style="color: #666; margin-bottom: 25px; font-size: 14px; line-height: 1.6;">
                    Manage queues, view analytics, and handle ticket operations. Authorized access only.
                </p>
                <a href="admin/login.php" class="btn btn-primary btn-main" style="background: #6f42c1;">
                    Admin Login
                </a>
            </div>

        </div>

        <!-- INFO SECTION -->
        <div style="margin-top: 50px; padding: 25px; background: #f8f9fa; border-radius: 12px; border-left: 4px solid #0d6efd; text-align: center;">
            <p style="margin: 0; color: #666; font-size: 14px;">
                This system provides an efficient queue management solution for better customer experience.
            </p>
        </div>

    </div>

</div>

<?php include "includes/footer.php"; ?>
