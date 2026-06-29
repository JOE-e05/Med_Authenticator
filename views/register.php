<?php include 'includes/header.php'; ?>

<main>

<section class="form-container">

    <div class="form-card">

        <h1>Create an Account</h1>

        <p>Register to start verifying medicines.</p>

        <form action="#" method="POST">

            <input
                type="text"
                name="fullname"
                placeholder="Full Name"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Email Address"
                required
            >

            <input
                type="text"
                name="phone"
                placeholder="Phone Number"
                required
            >

            <select name="role" required>

                <option value="">Select Role</option>

                <option value="customer">Customer</option>

                <option value="pharmacist">Pharmacist</option>

            </select>

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm Password"
                required
            >

            <button
                type="submit"
                class="btn-primary"
            >
                Register
            </button>

        </form>

        <p class="form-link">
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </div>

</section>

</main>

<?php include 'includes/footer.php'; ?>