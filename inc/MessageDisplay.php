<?php

class MessageDisplay

/**
 * Displays a formatted message using Bootstrap alert classes.
 * @param string $expression - The expression to display in the message.
 * @param string $username - The username associated with the message.
 * @param string $message - The main message content to be displayed.
 * @param string $messageType - The type of message (e.g., success, warning, danger).
 * @param array $optionalParams - Optional parameters (e.g., wrongly entered IDs).
 */


{
    public function showMessage(string $expression, string $username, string $message, string $messageType, array $optionalParams)
    { ?>

        <div class="alert alert-<?php echo $messageType ?> alert-dismissible fade show" role="alert">
            <strong><?php echo $expression ?> <?php echo $username . ' ' ?></strong>
            <?php
            echo $message;
            if (!empty($optionalParams)) {
                echo "<span class='text-danger'> But the following wrongly entered IDs were skipped:</span> " . implode(',', $optionalParams);
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
    }
}
