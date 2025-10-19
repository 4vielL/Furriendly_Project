<?php
session_start();
require '../php/db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../html/auth.php?error=Please log in first.");
    exit();
}

$username = $_SESSION['username'];
$user = null;

// Fetch user info for navbar
$isLoggedIn = isset($_SESSION['username']);
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

// Detect which columns exist in `pets` table so we can adapt to older/newer schemas
$cols_result = $conn->query("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pets'");
$existing_cols = [];
if ($cols_result) {
  while ($r = $cols_result->fetch_assoc()) $existing_cols[] = $r['COLUMN_NAME'];
}

$has_enhanced = (
  in_array('pet_breed', $existing_cols) && in_array('birthdate', $existing_cols) && in_array('medical_history', $existing_cols) && in_array('pet_profile_pic', $existing_cols)
);

// --- EDIT MODE LOGIC ---
$is_edit_mode = false;
$pet_to_edit = null;
if (isset($_GET['edit_id'])) {
    $is_edit_mode = true;
    $pet_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM pets WHERE id = ? AND username = ?");
    $stmt->bind_param('is', $pet_id, $username);
    $stmt->execute();
    $pet_to_edit = $stmt->get_result()->fetch_assoc();

    if (!$pet_to_edit) {
        // Pet not found or doesn't belong to the user, redirect
        header("Location: your_pet.php?error=petnotfound");
        exit();
    }
}
// Expected DB changes (if not present): pets table should have columns:
// id (PK), username, pet_name, pet_breed, birthdate (DATE), medical_history (TEXT), medical_records (VARCHAR), vaccines (TEXT JSON), medical_condition (VARCHAR), created_at

// Handle file upload helper
function upload_file($file_field, $target_dir = '../uploads/') {
    if (empty($_FILES[$file_field]['name'])) return null;
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $ext = pathinfo($_FILES[$file_field]['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target = $target_dir . $filename;
    if (move_uploaded_file($_FILES[$file_field]['tmp_name'], $target)) return $target;
    return null;
}

// --- Handle Pet Add ---
if (isset($_POST['add_pet'])) {
    $pet_name = $_POST['pet_name'];
    $pet_breed = $_POST['pet_breed'];
    // New fields from your form
    $pet_species = $_POST['pet_species'];
    $pet_gender = $_POST['pet_gender'];


    $birthdate = $_POST['birthdate']; // YYYY-MM-DD
    $medical_history = $_POST['medical_history'] ?? '';
    $medical_condition = $_POST['medical_condition'] ?? '';

    // vaccines: expect arrays vaccines_type[] and vaccines_date[]
    $vaccines = [];
    if (!empty($_POST['vaccine_type']) && is_array($_POST['vaccine_type'])) {
        foreach ($_POST['vaccine_type'] as $i => $type) {
            $date = $_POST['vaccine_date'][$i] ?? '';
            if ($type) $vaccines[] = ['type' => $type, 'date' => $date];
        }
    }
    $vaccines_json = json_encode($vaccines);

    // medical records upload (single file)
    $medical_records_path = upload_file('medical_records');
    // new pet profile pic upload
    $pet_profile_pic_path = upload_file('pet_profile_pic');

    if (isset($_POST['pet_id']) && $has_enhanced) { // UPDATE existing pet
        $pet_id = $_POST['pet_id'];
        // If a new picture is uploaded, use it. Otherwise, keep the old one.
        $pic_sql_part = $pet_profile_pic_path ? ", pet_profile_pic = ?" : "";

        $sql = "UPDATE pets SET pet_name=?, pet_breed=?, pet_species=?, pet_gender=?, birthdate=?, medical_history=?, vaccines=?, medical_condition=? {$pic_sql_part} WHERE id=? AND username=?";
        $stmt = $conn->prepare($sql);

        if ($pet_profile_pic_path) {
            $stmt->bind_param('sssssssssis', $pet_name, $pet_breed, $pet_species, $pet_gender, $birthdate, $medical_history, $vaccines_json, $medical_condition, $pet_profile_pic_path, $pet_id, $username);
        } else {
            $stmt->bind_param('ssssssssis', $pet_name, $pet_breed, $pet_species, $pet_gender, $birthdate, $medical_history, $vaccines_json, $medical_condition, $pet_id, $username);
        }
        $stmt->execute();

    } else if (isset($_POST['pet_id']) && !$has_enhanced) { // UPDATE existing pet (fallback schema)
        $pet_id = $_POST['pet_id'];
        $ageYears = '';
        if (!empty($birthdate)) {
          $birth = new DateTime($birthdate);
          $now = new DateTime();
          $diff = $now->diff($birth);
          $ageYears = (string)$diff->y;
        }

        $sql = "UPDATE pets SET pet_name=?, pet_species=?, pet_age=? WHERE id=? AND username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssis', $pet_name, $pet_species, $ageYears, $pet_id, $username);
        $stmt->execute();

    } else if ($has_enhanced) { // INSERT new pet (enhanced schema)
        $sql = "INSERT INTO pets (username, pet_name, pet_breed, pet_species, pet_gender, birthdate, medical_history, medical_records, vaccines, medical_condition, pet_profile_pic, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
          error_log('Prepare failed: ' . $conn->error); die('Database error.');
        }
        $stmt->bind_param('sssssssssss', $username, $pet_name, $pet_breed, $pet_species, $pet_gender, $birthdate, $medical_history, $medical_records_path, $vaccines_json, $medical_condition, $pet_profile_pic_path);
        $stmt->execute();

    } else { // INSERT new pet (fallback to older schema)
        // fallback to older schema: username, pet_name, pet_species, pet_age
        // compute pet_age in years from birthdate
        $ageYears = '';
        if (!empty($birthdate)) {
          $birth = new DateTime($birthdate);
          $now = new DateTime();
          $diff = $now->diff($birth);
          $ageYears = (string)$diff->y;
        }
        $sql = "INSERT INTO pets (username, pet_name, pet_species, pet_age) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
          error_log('Prepare failed (fallback): ' . $conn->error);
          die('Database error. Please contact the administrator.');
        }
        $stmt->bind_param('ssss', $username, $pet_name, $_POST['pet_species'], $ageYears);
        $stmt->execute();
    }

    header('Location: your_pet.php?success=1');
    exit();
}

function calculate_age_from_birthdate($birthdate) {
    if (empty($birthdate)) return '';
    try {$birth = new DateTime($birthdate);} catch (Exception $e) { return ''; }
    $now = new DateTime();
    $diff = $now->diff($birth);
    return $diff->y . ' yrs, ' . $diff->m . ' mos';
}

$medical_conditions = ['None', 'Diabetes', 'Allergy', 'Arthritis', 'Heart Disease', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FURRiendly | Pets</title>
  <link rel="stylesheet" href="../style.css">
  <script>
    // small helper to add vaccine rows
    document.addEventListener('DOMContentLoaded', function() {
      const popup = document.getElementById('medicalRecordPopup');
      const openBtn = document.getElementById('openMedicalPopupBtn');
      const closeElements = document.querySelectorAll('.popup-close');

      // --- Popup Controls ---
      openBtn.onclick = () => { popup.style.display = 'block'; };
      closeElements.forEach(el => el.onclick = () => { popup.style.display = 'none'; });
      window.onclick = (event) => { if (event.target == popup) popup.style.display = 'none'; };

      // --- Vaccine Logic ---
      const vaccineTypeInput = document.getElementById('popupVaccineType');
      const vaccineDateInput = document.getElementById('popupVaccineDate');
      const addVaccineBtn = document.getElementById('addVaccineToListBtn');
      const vaccineListDisplay = document.getElementById('vaccineListDisplay');
      const hiddenVaccineContainer = document.getElementById('hiddenVaccineInputs');
      const medicalHistoryTextarea = document.getElementById('medicalHistoryTextarea');

      addVaccineBtn.onclick = () => {
        const type = vaccineTypeInput.value.trim();
        const date = vaccineDateInput.value;
        if (!type || !date) {
          alert('Please provide both vaccine type and date.');
          return;
        }

        // 1. Add to visual display inside popup
        const displayItem = document.createElement('div');
        displayItem.className = 'list-display-item';
        displayItem.textContent = `${type} (Date: ${date})`;
        vaccineListDisplay.appendChild(displayItem);

        // Also append to the main medical history textarea for visual feedback
        medicalHistoryTextarea.value += `\n- Vaccine: ${type} (Date: ${date})`;

        // 2. Add to hidden inputs for form submission
        const hiddenInputs = document.createElement('div');
        hiddenInputs.innerHTML = `
          <input type="hidden" name="vaccine_type[]" value="${type}">
          <input type="hidden" name="vaccine_date[]" value="${date}">
        `;
        hiddenVaccineContainer.appendChild(hiddenInputs);

        // 3. Clear inputs
        vaccineTypeInput.value = '';
        vaccineDateInput.value = '';
        vaccineTypeInput.focus();
      };

      // --- Medical Condition Logic ---
      const conditionInput = document.getElementById('popupCondition');
      const addConditionBtn = document.getElementById('addConditionToListBtn');
      const conditionListDisplay = document.getElementById('conditionListDisplay');
      const hiddenMedicalConditions = document.getElementById('hiddenMedicalConditions');

      addConditionBtn.onclick = () => {
        const condition = conditionInput.value.trim();
        if (!condition) return;

        // Add to visual display in popup and append to hidden input
        conditionListDisplay.innerHTML += `<div class="list-display-item">${condition}</div>`;
        hiddenMedicalConditions.value += condition + '\n';
        // Also append to the main medical history textarea
        medicalHistoryTextarea.value += `\n- Condition: ${condition}`;
        conditionInput.value = '';
        conditionInput.focus();
      };

      // If in edit mode, calculate age on page load
      <?php if ($is_edit_mode): ?>
        updateAgePreview();
      <?php endif; ?>

    });

    // preview age from birthdate
    function updateAgePreview() {
      const bd = document.getElementById('birthdate').value;
      const out = document.getElementById('agePreview');
      if (!bd) { out.value = ''; return; }
      const birth = new Date(bd);
      const now = new Date();
      let years = now.getFullYear() - birth.getFullYear();
      let months = now.getMonth() - birth.getMonth();
      if (months < 0) { years--; months += 12; }
      out.value = years + ' yrs, ' + months + ' mos';
    }

    // preview pet profile picture
    function previewPetImage(event) {
      const reader = new FileReader();
      reader.onload = function(){
        const output = document.getElementById('petPicPreview');
        output.src = reader.result;
      };
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="nav-left"><h4>FURRiendly</h4></div>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="contact.php">Contact</a></li>
      </ul>
      <div class="auth-butt">
        <?php if ($isLoggedIn && $user): ?>
            <div class="user-dropdown">
                <a href="#" class="profile-link">
                    <img src="<?php echo htmlspecialchars(!empty($user['profile_pic']) ? $user['profile_pic'] : '../images/default-avatar.png'); ?>" alt="Profile">
                    <span class="profile-name"><?php echo htmlspecialchars($user['name'] ?? $username); ?></span>
                </a>
                <div class="dropdown-content">
                    <a href="dashboard.php">Edit Profile</a>
                    <a href="your_pet.php">Pet Profile</a>
                    <a href="events.php">Events</a>
                    <a href="myhosting.php">My Hosting</a>
                    <hr>
                    <a href="../php/logout.php">Logout</a>
                </div>
            </div>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <main class="dashboard-body">
    <div class="dashboard-container">
      <h2>🐾 Pet Profile</h2>

      <div class="pet-pic-container">
        <img src="<?php echo htmlspecialchars($pet_to_edit['pet_profile_pic'] ?? '../images/default-pet-avatar.png'); ?>" id="petPicPreview" class="pet-profile-pic" alt="Pet Profile Picture Preview">
        <label for="pet_profile_pic_upload" class="upload-btn-label">Upload Picture</label>
        <input type="file" name="pet_profile_pic" id="pet_profile_pic_upload" accept="image/*" onchange="previewPetImage(event)">
      </div>

      <?php if (isset($_GET['success'])): ?>
        <p class="success-msg">Pet added successfully.</p>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="pet-form" oninput="updateAgePreview()">
        <?php if ($is_edit_mode): ?>
            <input type="hidden" name="pet_id" value="<?php echo $pet_to_edit['id']; ?>">
        <?php endif; ?>

        <!-- Pet Name -->
        <label for="pet_name" style="text-align: left;">Pet Name</label>
        <input type="text" name="pet_name" placeholder="Pet Name" value="<?php echo htmlspecialchars($pet_to_edit['pet_name'] ?? ''); ?>" required>

        <!-- Age and Gender -->
        <div class="form-row">
          <div class="form-group half">
            <label for="age_preview">Age</label>
            <input type="text" id="agePreview" name="age_preview" placeholder="(Age) (auto-calculated)" readonly>
          </div>
          <div class="form-group half">
            <label for="pet_gender">Gender</label>
            <select id="pet_gender" name="pet_gender" required>
              <option value="Male" <?php if(isset($pet_to_edit['pet_gender']) && $pet_to_edit['pet_gender'] == 'Male') echo 'selected'; ?>>Male</option>
              <option value="Female" <?php if(isset($pet_to_edit['pet_gender']) && $pet_to_edit['pet_gender'] == 'Female') echo 'selected'; ?>>Female</option>
            </select>
          </div>
        </div>

        <!-- Birthdate, Species, Breed -->
        <label for="birthdate" style="text-align: left;">Birthdate</label> 
        <input id="birthdate" type="date" name="birthdate" value="<?php echo htmlspecialchars($pet_to_edit['birthdate'] ?? ''); ?>" >
        <label for="pet_species" style="text-align: left;">Species</label>
        <input type="text" name="pet_species" placeholder="(Species)" value="<?php echo htmlspecialchars($pet_to_edit['pet_species'] ?? ''); ?>" >
        <label for="pet_breed" style="text-align: left;">Breed</label>
        <input type="text" name="pet_breed" placeholder="(Breed)" value="<?php echo htmlspecialchars($pet_to_edit['pet_breed'] ?? ''); ?>" 

        <!-- Medical History and Record Button -->
        <div class="form-label-button-row">
          <label for="medical_history" style="text-align: left;">Medical History</label>
          <button type="button" id="openMedicalPopupBtn" class="medical-record-btn">Add Medical Record</button>
        </div>
        <textarea id="medicalHistoryTextarea" name="medical_history" rows="5" placeholder="Medical History."><?php echo htmlspecialchars($pet_to_edit['medical_history'] ?? ''); ?></textarea>
        
        <!-- Hidden container for vaccine data from popup -->
        <div id="hiddenVaccineInputs">
            <?php if ($is_edit_mode && !empty($pet_to_edit['vaccines'])):
                $saved_vaccines = json_decode($pet_to_edit['vaccines'], true);
                if (is_array($saved_vaccines)) {
                    foreach ($saved_vaccines as $vaccine): ?>
                        <input type="hidden" name="vaccine_type[]" value="<?php echo htmlspecialchars($vaccine['type']); ?>">
                        <input type="hidden" name="vaccine_date[]" value="<?php echo htmlspecialchars($vaccine['date']); ?>">
            <?php   endforeach;
                }
            endif; ?>
        </div>
        <textarea name="medical_condition" id="hiddenMedicalConditions" style="display:none;"><?php echo htmlspecialchars($pet_to_edit['medical_condition'] ?? ''); ?></textarea>

        <button type="submit" name="add_pet">
            <?php echo $is_edit_mode ? 'Save Changes' : 'Add Pet'; ?>
        </button>
        <a href="your_pet.php" class="back-link">Back</a>
      </form>

    </div>

    <!-- The Medical Record Popup -->
    <div id="medicalRecordPopup" class="popup">
      <div class="popup-content">
        <span class="close popup-close">&times;</span>
        <h3>Add Medical Details</h3>

        <!-- Vaccine Section -->
        <div class="popup-section">
          <div class="popup-form-row">
            <label for="popupVaccineType">Vaccination Type</label>
            <input type="text" id="popupVaccineType" placeholder="e.g., Rabies">
          </div>
          <div class="popup-form-row">
            <label for="popupVaccineDate">Date</label>
            <input type="date" id="popupVaccineDate">
          </div>
          <button type="button" id="addVaccineToListBtn" class="popup-add-btn">Add Vaccine to Record</button>
          <div id="vaccineListDisplay" class="list-display-box"></div>
        </div>

        <!-- Medical Condition Section -->
        <div class="popup-section">
          <label class="popup-section-label">Medical Condition</label>
          <div class="popup-form-row">
            <label for="popupCondition">Condition</label>
            <input type="text" id="popupCondition" placeholder="e.g., Allergy">
          </div>
          <button type="button" id="addConditionToListBtn" class="popup-add-btn">Add Condition to Record</button>
          <div id="conditionListDisplay" class="list-display-box"></div>
        </div>

        <a href="#" class="popup-close popup-back-link">Back</a>
      </div>
    </div>

  </main>
</body>
</html>