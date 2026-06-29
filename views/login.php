<?php include 'includes/header.php'; ?>

<main>

<section class="form-container">

    <div class="form-card">

        <h1>Welcome Back</h1>

        <p>Login to access your Med Authenticator account.</p>

        <form action="#" method="POST">

            <input
                type="email"
                name="email"
                placeholder="Email Address"
                required
            >

            <input
                type="password"
                name="password"
                id="password"
                placeholder="Password"
                required
            >

            <button
                type="button"
                class="toggle-password"
                onclick="togglePassword()"
            >
                Show Password
            </button>

            <button
                type="submit"
                class="btn-primary"
            >
                Login
            </button>

        </form>

        <p class="form-link">
            Don't have an account?
            <a href="register.php">Register</a>
        </p>

    </div>

</section>

</main>

<?php include 'includes/footer.php'; ?>