<?php
session_start();

include(__DIR__ . "/includes/header.php");
?>

<div class="container mt-5">

    <!-- ABOUT US -->
    <h2 class="mb-3">About Us</h2>
    <p>
        i-rate_movies is a movie review platform where users can explore films,
        share their opinions with the online film enthusiasts community, and read the reviews from other users before choosing what to watch.
    </p>
    <p>
        Users can rate movies from 1–5 stars, leave comments, and manage their reviews.
        Reviews can be edited or deleted at any time via the movie page or account page.
    </p>
    <p>
Once a user contributes 11 or more reviews, they are automatically recognised as a “Super Reviewer” and awarded a badge along with certain unique privileges.
Users who review more films also tend to watch more films and, statistically speaking, often have more relevant knowledge and experience to draw upon.
So what are you waiting for? Submit 11+ reviews ASAP and earn a 🏅 badge you can show to family, friends, and colleagues proudly as a way of validating your
expertise as one of our most seasoned critics.
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
                What is the benefit of being a Super Reviewer?
            </button>
            <div id="faq5" class="accordion-collapse collapse">
                <div class="accordion-body">
                    The main benefit to earning the title of 🏅 Super Reviewer is not necessarily something the associated user receives, but more as a nett benefit
                    to the community as a whole because 🏅 Super Reviewers reviews place above non-Super Reviewers reviews on movie pages. This makes it more likely that users will see 
                    and benefit from the opinions of our most active and engaged movie reviewers. Obviously there are also the side-benefits of gamifying the application
                    by incentivising users to write more regular reviews in order to earn the 🏅 Super Reviewer badge and obtain the associated recognition status plus 
                    keeping more users returning to our platform which helps to elevate us in the organic search engine rankings which adds to our popularity and growth.
            </div>
        </div>

        <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                Why can't I submit a review?
            </button>
            <div id="faq6" class="accordion-collapse collapse">
                <div class="accordion-body">
                    You must be logged in to submit a review. If you are logged in and still
                    experiencing issues, try refreshing the page.
                </div>
            </div>
        </div>

                <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                Can I permanently delete my account and all associated data?
            </button>
            <div id="faq7" class="accordion-collapse collapse">
                <div class="accordion-body">
                    Yes, you can permanently delete your account and all associated data at any time from your account settings.
                </div>
            </div>
        </div>

                <div class="accordion-item">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                Is it safe to share my personal information with i-rate_movies?
            </button>
            <div id="faq8" class="accordion-collapse collapse">
                <div class="accordion-body">
                    We are committed to protecting your privacy and handling your data responsibly. Your personal information is never sold or shared with third parties.
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