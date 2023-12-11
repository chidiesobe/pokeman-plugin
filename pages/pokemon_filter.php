  <div class="container mt-3">
      <div class="text-primary">
          <h4 class="text-primary">Pokemon Filter</h4>
          <!-- Accordion for displaying Pokemon Search Guide -->
          <div class="accordion" id="accordion">
              <div class="accordion-item">
                  <h2 class="accordion-header" id="headingOne">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                          <span class="text-danger">Please take a moment to familiarize yourself with the Pokemon Search Guide.</span>
                      </button>
                  </h2>
                  <!-- List of guidelines for searching Pokemon -->
                  <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordion">
                      <div class="accordion-body">
                          <ul class="list-group">
                              <li class="list-group-item">Only enter whole numbers.</li>
                              <li class="list-group-item">All values must be unique.</li>
                              <li class="list-group-item">All Pokemon IDs must be numeric.</li>
                              <li class="list-group-item">Do not end a search with a comma.</li>
                              <li class="list-group-item">Do not start a search with a comma.</li>
                              <li class="list-group-item">You do not need to leave white spaces between IDs for clearity.</li>

                          </ul>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <br>
      <!-- Form for entering Pokemon IDs -->
      <form method="POST">
          <!-- Check if the form is submitted and process the form -->
          <?php
            if (
                $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['pokemon_ids']) && isset($_POST['pokemon_ids'])
                && isset($_POST['form_submitted']) && $_POST['form_submitted'] == "true"
            ) {
                $this->formProcessor->processForm();
            }
            ?>
          <br>
          <!-- Verify submitted input using nonce verification -->
          <input type="hidden" name="form_submitted" value="true">
          <?php wp_nonce_field('verifyPokemonID', 'pokeNonce') ?>

          <label for="pokemon_ids" class="form-label">
              <p class="mb-1"><strong>Please Enter Your Preferred Comma-Separated Pokeman ID's:</strong></p>
          </label>
          <textarea class="form-control mb-2" name="pokemon_ids" placeholder="Samples IDs 3,30,2"></textarea>
          <input type="submit" name="submit" value="Search" class="btn btn-sm btn-secondary">
      </form>
      <br>

      <!-- Displaying search results -->
      <?php

        if (isset($this->formProcessor->getCleanedID)) {

            $this->apiPreprocessor->setPokemonApiCall($this->formProcessor->getCleanedID);
            $response =  $this->apiPreprocessor->getPokemonApiCall();

            if (!empty($this->formProcessor->getCleanedID)) {
                echo '<div class="row">';

                foreach ($response as $pokemon) {
        ?>
                  <div class="col-sm-6 mb-3 mb-sm-0">
                      <div class="card">
                          <div class="card-body">
                              <h5 class="card-title">Pokemon's Name:<?= ' ' . ucfirst($pokemon['name']) ?></h5>
                              <p class="card-text"><?= ' ' . ucfirst($pokemon['name']) ?> is a Pokemon with <strong><?= count($pokemon['movesName']) ?></strong> moves, with the following abilities: <strong><?= ' ' . implode(',', $pokemon['abilityName']) . '.' ?></strong></p>
                              <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#<?= $pokemon['name'] ?>">
                                  show all moves
                              </button>
                              <!-- Modal to display all moves -->
                              <div class="modal fade" id="<?= $pokemon['name'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                  <div class="modal-dialog">
                                      <div class="modal-content">
                                          <div class="modal-header">
                                              <h5 class="modal-title">All <?= ' ' . ucfirst($pokemon['name']) ?> Moves</h5>
                                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                          </div>
                                          <div class="modal-body">
                                              <p class="text-center"><?= implode(',', $pokemon['movesName']) ?></p>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
      <?php
                }
            }
            echo '</div>';
        } else {
            echo 'No search yet';
        }

        ?>
  </div>