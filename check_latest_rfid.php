<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>RFID Voucher Scan</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px 20px;
      background: linear-gradient(90deg, #38A169, #34996d);
      min-height: 100vh;
    }

    .container {
      max-width: 700px;
      margin: 0 auto;
      text-align: center;
    }

    .logo-placeholder img {
      max-width: 110px;
      max-height: 110px;
      margin-bottom: 20px;
    }

    h1 {
      color: #fff;
      text-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }

    #rfid {
      width: 300px;
      height: 40px;
      font-size: 1.5em;
      margin-bottom: 20px;
    }

    .photo-frame {
      width: 400px;
      height: 400px;
      margin: 20px auto;
      border: 3px solid #000;
      background-color: #e0e0e0;
      position: relative;
    }

    .student-photo {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: none;
    }

    .placeholder-photo {
      position: absolute;
      width: 100%;
      height: 100%;
      color: #888;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 20px;
      background-color: #e0e0e0;
    }

    .info-box {
      max-width: 400px;
      margin: auto;
      text-align: left;
    }

    .info-row {
      margin-bottom: 20px;
    }

    .info-row label {
      font-weight: bold;
      color: #fff;
      display: block;
      margin-bottom: 5px;
    }

    .info-row input {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 5px;
      font-size: 1rem;
      background-color: #f0fdf4;
    }

    .voucher-row {
      display: flex;
      justify-content: space-between;
      color: #fff;
      font-weight: bold;
      margin-top: 10px;
    }

    .error-message {
      display: none;
      color: #fff;
      background: rgba(229, 62, 62, 0.8);
      padding: 10px;
      border-radius: 8px;
      margin-top: 15px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo-placeholder">
      <img src="isu-logo.png" alt="University Logo" onerror="this.src='fallback.png'">
    </div>
    <h1>Isabela State University Voucher System</h1>

    <input type="text" id="rfid" autofocus autocomplete="off" />

    <div class="error-message" id="error_message"></div>

    <div class="photo-frame">
      <img id="student_photo" class="student-photo" />
      <div id="photo_placeholder" class="placeholder-photo">Tap Your ID</div>
    </div>

    <div class="info-box">
      <div class="info-row">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" readonly>
      </div>
      <div class="info-row">
        <label for="student_id">Student ID</label>
        <input type="text" id="student_id" readonly>
      </div>
      <div class="voucher-row">
        <span>Voucher Code:</span>
        <span id="voucher_code"></span>
      </div>
    </div>
  </div>

  <script>
    const rfidInput = document.getElementById("rfid");
    const errorMessage = document.getElementById("error_message");
    const fullName = document.getElementById("full_name");
    const studentId = document.getElementById("student_id");
    const voucherCode = document.getElementById("voucher_code");
    const studentPhoto = document.getElementById("student_photo");
    const photoPlaceholder = document.getElementById("photo_placeholder");

    function resetDisplay() {
      fullName.value = '';
      studentId.value = '';
      voucherCode.textContent = '';
      studentPhoto.style.display = 'none';
      photoPlaceholder.style.display = 'flex';
    }

    function handleRFIDScan(rfid) {
      fetch('rfid_scan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfid=' + encodeURIComponent(rfid)
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          const { student, voucher } = data;
          fullName.value = `${student.last_name}, ${student.first_name}`;
          studentId.value = student.student_id;
          voucherCode.textContent = voucher ? voucher.voucher_code : 'No Available Voucher';

          if (student.picture) {
            studentPhoto.src = "uploads/" + student.picture;
            studentPhoto.style.display = 'block';
            photoPlaceholder.style.display = 'none';
          } else {
            studentPhoto.style.display = 'none';
            photoPlaceholder.style.display = 'flex';
          }

          errorMessage.style.display = 'none';
        } else {
          errorMessage.textContent = data.message;
          errorMessage.style.display = 'block';
          resetDisplay();
        }
      })
      .catch(err => {
        console.error("Fetch error:", err);
        errorMessage.textContent = "An unexpected error occurred.";
        errorMessage.style.display = 'block';
        resetDisplay();
      });
    }

    rfidInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        const rfid = rfidInput.value.trim();
        if (rfid) {
          handleRFIDScan(rfid);
          rfidInput.value = '';
        }
      }
    });

    window.onload = () => rfidInput.focus();
    document.body.addEventListener("click", () => rfidInput.focus());
  </script>
</body>
</html>
