<div class="container mt-3">
    <div class="text-primary">
        <h4 class="text-primary">API URL</h4>
    </div>

    <form method="POST">
        <!-- if user submits the form -->
        <?php
        if (
            $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['api_url']) && isset($_POST['api_url'])
            && isset($_POST['form_submitted']) && $_POST['form_submitted'] == "true"
        ) {
            $this->apiProcessor->setSecureAPI($_POST['api_url']);
        }
        ?>
        <br>
        <!-- Verify submited input  -->
        <input type="hidden" name="form_submitted" value="true">
        <?php wp_nonce_field('verifyAPIURL', 'pokeAPINonce') ?>

        <label for="api_url" class="form-label">
            <p class="mb-1 text-danger"><strong>Store API URL</strong></p>
        </label>
        <input class="form-control mb-2" name="api_url" value=<?php echo get_option('secure_api_data') ?> />

        <input type="submit" name="submit" value="Store API URL" class="btn btn-sm btn-danger">
    </form>
</div>