<?php

require_once __DIR__ . '/../classes/verifier.php';

$result = null;

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $verifier = new SystemVerifier();

    $result = $verifier->checkBatchNumber($_POST['batch_number']);

}

include 'includes/header.php';

?>

<main>

<section class="form-container">

    <div class="form-card">

        <h1>Verify Medicine</h1>

        <p>Enter the medicine batch number below.</p>

        <form method="POST">

            <input
                type="text"
                name="batch_number"
                placeholder="Enter Batch Number"
                required
            >

            <button
                type="submit"
                class="btn-primary"
            >
                Verify Medicine
            </button>

        </form>

        <?php if($result): ?>

        <div class="result <?php echo strtolower($result['status']); ?>">

            <h2><?php echo $result['status']; ?></h2>

            <p><?php echo $result['message']; ?></p>

        </div>

        <?php endif; ?>

    </div>

</section>

</main>

<?php include 'includes/footer.php'; ?>