<div class="container mt-3">
    <div class="text-primary">
        <h4 class="text-primary">Pokemon Logs</h4>
    </div>

    <form method="POST">
        <!-- if user submits the form -->
        <?php
        if (
            $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['log_ids']) && isset($_POST['log_ids'])
            && isset($_POST['form_submitted']) && $_POST['form_submitted'] == "true"
        ) {
            $this->logsProcessor->processLogs($_POST['log_ids']);
        }
        ?>
        <br>
        <!-- Verify submited input  -->
        <input type="hidden" name="form_submitted" value="true">
        <?php wp_nonce_field('verifyLogIDs', 'pokeLogNonce') ?>

        <label for="log_ids" class="form-label">
            <p class="mb-1 text-danger"><strong>All ID's must be entered on a newline:</strong></p>
        </label>
        <textarea class="form-control mb-2" name="log_ids" rows="15">
                    <?php
                    $file_content = file_get_contents($this->logsProcessor->getLogFilePath);
                    echo  esc_textarea($file_content);
                    ?>
                </textarea>
        <input type="submit" name="submit" value="Update Log" class="btn btn-sm btn-secondary">
    </form>
</div>