<?php
session_start();

include(__DIR__ . "/includes/header.php");
?>

<div class="container mt-5">

    <!-- ABOUT US -->
    <h2 class="mb-3">About Us</h2>
    <p>
        I-rate Movies is a movie review platform where users can explore films,
        share their opinions, and discover what others think before watching.
    </p>
    <p>
        Users can rate movies from 1–5 stars, leave comments, and manage their reviews.
        Reviews can be edited or deleted at any time via the movie page or account page.
    </p>
    <p>
        Active contributors are recognised through our <strong>Super Reviewer</strong>
        system. Users who submit 11 or more reviews earn a 🏅 badge displayed alongside
        their name.
    </p>

    <hr class="my-5">

    <!-- HELP / FAQ -->
    <h2 class="mb-3">Help & FAQ</h2>

    <div class="accordion" id="faqAccordion">

        <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                How do I create an account?
            </button>
            <div id="faq1" class="accordion-collapse collapse">
                <div class="accordion-body">
                    Click "Sign Up" in the top-right corner and complete the registration form.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                How do I leave a review?
            </button>
            <div id="faq2" class="accordion-collapse collapse">
                <div class="accordion-body">
                    Go to a movie page, select a star rating, and optionally add a comment.
                    You must be logged in to submit a review.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                Can I edit or delete my reviews?
            </button>
            <div id="faq3" class="accordion-collapse collapse">
                <div class="accordion-body">
                    Yes. You can edit or delete your reviews from the movie page or your account page at any time.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                What is a Super Reviewer?
            </button>
            <div id="faq4" class="accordion-collapse collapse">
                <div class="accordion-body">
                    Users who submit 11 or more reviews receive a 🏅 Super Reviewer badge,
                    highlighting their contribution to the community.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                Why can't I submit a review?
            </button>
            <div id="faq5" class="accordion-collapse collapse">
                <div class="accordion-body">
                    You must be logged in to submit a review. If you are logged in and still
                    experiencing issues, try refreshing the page.
                </div>
            </div>
        </div>

    </div>

    <hr class="my-5">

    <!-- PRIVACY -->
    <h2 class="mb-3">Privacy & Data Use</h2>

    <p>
        We are committed to protecting your privacy and handling your data responsibly.
        This section outlines what data we collect and how it is used.
    </p>

    <h5 class="mt-4">What data we collect</h5>
    <ul>
        <li>Account information (e.g. alias, login credentials)</li>
        <li>Reviews and ratings you submit</li>
        <li>Basic usage data to improve the platform</li>
    </ul>

    <h5 class="mt-4">How we use your data</h5>
    <ul>
        <li>To provide and maintain your account</li>
        <li>To display your reviews and ratings publicly</li>
        <li>To improve site functionality and user experience</li>
    </ul>

    <h5 class="mt-4">Your rights</h5>
    <p>
        In line with data protection laws (including GDPR), you have the right to:
    </p>
    <ul>
        <li>Access the data we hold about you</li>
        <li>Request correction of inaccurate data</li>
        <li>Request deletion of your account and associated data</li>
    </ul>

    <h5 class="mt-4">Data retention</h5>
    <p>
        We retain your data only for as long as your account is active.
        If you delete your account, your personal data will be removed.
    </p>

    <h5 class="mt-4">Data sharing</h5>
    <p>
        We do not sell or share your personal data with third parties.
        Obviously your reviews are publicly visible, but your personal information (private account data) is not shared outside our organisation.
    </p>

</div>

<?php include("includes/footer.php"); ?>