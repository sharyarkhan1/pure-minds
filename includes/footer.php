<?php
if (!isset($site_name)) {
    include_once __DIR__ . '/config.php';
}
?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-widgets">
            <div class="row">

                <!-- Footer About -->
                <div class="col-lg-5 col-md-12">
                    <div class="footer-about">

                        <div class="footer-logo">
                            <a href="<?php echo htmlspecialchars($site_url . '/'); ?>">
                                <img src="images/footer-logo.png?v=4"
                                    alt="<?php echo htmlspecialchars($site_name); ?>">
                            </a>
                        </div>

                        <p>
                            Pure Minds Medical and Psychiatric Health LLC combines
                            board-certified psychiatric care and general medicine for
                            true whole-person well-being in Pickerington, Ohio.
                        </p>

                    </div>
                </div>


                <!-- Behavioral Health -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="footer-menu">

                        <h6>Behavioral Health</h6>

                        <ul>
                            <li><a href="#">Psychiatric Evaluations</a></li>
                            <li><a href="#">Medication Management</a></li>
                            <li><a href="#">Depression &amp; Anxiety</a></li>
                            <li><a href="#">Bipolar Disorder</a></li>
                            <li><a href="#">ADHD</a></li>
                            <li><a href="#">PTSD</a></li>
                            <li><a href="#">Sleep &amp; Stress</a></li>
                            <li><a href="#">Psychotherapy</a></li>
                        </ul>

                    </div>
                </div>


                <!-- Medical Care -->
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="footer-menu">

                        <h6>Medical Care</h6>

                        <ul>
                            <li><a href="#">Primary Care</a></li>
                            <li><a href="#">Urgent Care</a></li>
                            <li><a href="#">Family Medicine</a></li>
                            <li><a href="#">Chronic Care</a></li>
                            <li><a href="#">School &amp; Employment Physicals</a></li>
                        </ul>

                    </div>
                </div>


                <!-- Quick Links -->
                <div class="col-lg-3 col-md-4 col-sm-12">
                    <div class="footer-menu">

                        <h6>Quick Links</h6>

                        <ul>
                            <li><a href="#about-us">About</a></li>
                            <li><a href="#">Telehealth</a></li>
                            <li><a href="#">New Patients</a></li>
                            <li><a href="#">Insurance</a></li>
                            <li><a href="#contact-us">Contact</a></li>
                            <li><a href="<?php echo htmlspecialchars($site_url . '/book-an-appointment'); ?>">Book Appointment</a></li>
                        </ul>

                    </div>
                </div>

            </div>
        </div>
        <div class="footer-copyrights">
            <p>© 2026 Pure Minds. All rights reserved.</p>
            <p>Designed &amp; Developed by <a href="https://www.creativewebsitesolution.co.uk/" target="_blank" rel="noopener noreferrer">CreativeWebsiteSolution</a></p>
        </div>
    </div>
</footer>
</div>
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!-- Owl Carousel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

<!-- Bootstrap Library -->
<script src="js/bootstrap.min.js"></script>

<!-- Custom Scripts -->
<script src="js/script.js"></script>
</body>
</html>