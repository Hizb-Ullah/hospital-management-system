<?php
// hospital/pages/contact.php
$page_title = 'Contact & Medical Support';
require_once '../includes/header.php';
?>

<div class="fade-in">
    <div class="section-title">
        <h2>Get in Touch with Us</h2>
        <p>Do you have inquiries about our clinical facilities, surgery scheduling, or billing? Drop us a message.</p>
    </div>

    <!-- Double Column Split -->
    <div class="dashboard-grid-split" style="grid-template-columns: 1.2fr 1fr; margin-bottom: 4rem;">
        <!-- Contact Message Form -->
        <div class="card-panel">
            <h3>Send us a Message</h3>
            <form id="contact-message-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact-name">Full Name *</label>
                        <input type="text" id="contact-name" class="form-control" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="form-group">
                        <label for="contact-email">Email Address *</label>
                        <input type="email" id="contact-email" class="form-control" placeholder="john@example.com" required autocomplete="email">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contact-phone">Phone Number</label>
                    <input type="tel" id="contact-phone" class="form-control" placeholder="e.g. +1 555-0199">
                </div>

                <div class="form-group">
                    <label for="contact-subject">Subject *</label>
                    <input type="text" id="contact-subject" class="form-control" placeholder="e.g. Inquiries regarding Orthopedics Department" required>
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label for="contact-message">Your Message *</label>
                    <textarea id="contact-message" class="form-control" rows="5" placeholder="Detail your inquiries here..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>

        <!-- Contact Cards details -->
        <div>
            <div class="card-panel" style="margin-bottom: 2rem;">
                <h3>Our Location</h3>
                <p style="margin-bottom: 1.5rem;">📍 Medicare Clinic Center, 123 Health Ave, Metro City</p>
                <div style="background:var(--bg-main); border-radius:var(--radius-md); height: 200px; display:flex; align-items:center; justify-content:center; border:1px solid var(--border-color);">
                    <!-- Flat Vector SVG representing Map Placeholder -->
                    <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                        <line x1="9" y1="3" x2="9" y2="18"/>
                        <line x1="15" y1="6" x2="15" y2="21"/>
                    </svg>
                </div>
            </div>

            <div class="card-panel">
                <h3>Direct Contacts</h3>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 1rem; opacity: 0.9;">
                    <li>📞 <strong>Support Desk:</strong> +1 555 100 2000</li>
                    <li>📞 <strong>Emergency Ward:</strong> +1 555 100 9111</li>
                    <li>✉️ <strong>General Inquiries:</strong> contact@medicare.com</li>
                    <li>⏰ <strong>Office Hours:</strong> Mon - Sat: 8:00 AM - 8:00 PM</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('contact-message-form');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Simple mockup validation
            const name = document.getElementById('contact-name').value.trim();
            const email = document.getElementById('contact-email').value.trim();
            const subject = document.getElementById('contact-subject').value.trim();
            const message = document.getElementById('contact-message').value.trim();

            if (!name || !email || !subject || !message) {
                Toast.error('Please fill in all required fields.');
                return;
            }

            Toast.success('Thank you! Your inquiry has been sent. We will respond shortly.');
            form.reset();
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
