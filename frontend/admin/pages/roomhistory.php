<?php
include '../../../backend/dbconnection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Allocation</title>
    <link rel="stylesheet" href="../../../global.css">
    <link rel="stylesheet" href="../CSS/modern-admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../javascript/script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        .room-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .room-card {
            width: 200px;
            height: 180px;
            background: #f4f4f4;
            border-radius: 12px;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            transition: 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .room-full {
            background: #ff4d4d !important;
            color: white;
        }

        .room-icon {
            font-size: 30px;
            color: #3498db;
        }

        .room-number {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .beds {
            display: flex;
            justify-content: center;
            gap: 10px;
            width: 100%;
        }

        .bed {
            font-size: 24px;
            color: #2ecc71;
        }

        .bed.empty {
            color: #ccc;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.3);
            width: 350px;
            text-align: center;
        }

        .close {
            float: right;
            cursor: pointer;
            font-size: 20px;
            color: red;
        }

        #studentList {
            list-style-type: none;
            padding: 0;
        }

        #studentList li {
            background: #f4f4f4;
            margin: 5px 0;
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
        }

        .selected-student {
            background: #3498db !important;
            color: white;
        }

        #availableRoomsDropdown {
            display: none;
            margin-top: 10px;
            padding: 8px;
            width: 100%;
        }

        #changeRoomBtn {
            display: none;
            margin-top: 10px;
            padding: 8px 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>
<div class="admin-content">
    <h3>Room Allocation</h3>
    <div class="room-container">
        <?php
        $query = "SELECT r.room_id, r.room_number, 
                  (SELECT COUNT(*) FROM users WHERE users.room_id = r.room_id) AS occupied_beds
                  FROM rooms r";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $occupiedBeds = $row['occupied_beds'];
            $isFull = ($occupiedBeds == 4) ? 'room-full' : '';

            echo "<div class='room-card $isFull' data-room='{$row['room_id']}'>
                    <i class='fas fa-door-open room-icon'></i>
                    <div class='room-number'>Room {$row['room_number']}</div>
                    <div class='beds'>
                        <i class='fas fa-bed bed " . ($occupiedBeds >= 1 ? '' : 'empty') . "'></i>
                        <i class='fas fa-bed bed " . ($occupiedBeds >= 2 ? '' : 'empty') . "'></i>
                        <i class='fas fa-bed bed " . ($occupiedBeds >= 3 ? '' : 'empty') . "'></i>
                        <i class='fas fa-bed bed " . ($occupiedBeds >= 4 ? '' : 'empty') . "'></i>
                    </div>
                  </div>";
        }
        ?>
    </div>

    <div id="roomModal" class="modal">
        <span class="close"><i class="fas fa-times"></i></span>
        <h3>Students in Room <span id="roomNumber"></span></h3>
        <div class="modal-content">
            <ul id="studentList"></ul>
            <select id="availableRoomsDropdown"></select>
            <button id="changeRoomBtn">Change Room</button>
        </div>
    </div>
</div>

<script>
   $(document).ready(function () {
    let selectedStudent = null;

    $(".room-card").on("click", function () {
        let room_id = $(this).data("room");

        $.ajax({
            url: "fetch_students.php",
            type: "POST",
            data: { room_id: room_id },
            success: function (response) {
                $("#studentList").html(response);
                $("#roomModal").show();
                $("#roomNumber").text(room_id);
            }
        });
    });

    $("#studentList").on("click", "li", function () {
        $("#studentList li").removeClass("selected-student");
        $(this).addClass("selected-student");
        selectedStudent = $(this).data("student-id");

        $.ajax({
            url: "fetch_available_rooms.php",
            type: "GET",
            success: function (response) {
                let rooms = JSON.parse(response);
                let dropdown = $("#availableRoomsDropdown");
                dropdown.html('<option value="">Select a room</option>');

                rooms.forEach(room => {
                    dropdown.append(`<option value="${room.room_id}">Room ${room.room_number}</option>`);
                });

                dropdown.show();
                $("#changeRoomBtn").show();
            }
        });
    });

    $("#changeRoomBtn").on("click", function () {
        let newRoomId = $("#availableRoomsDropdown").val();
        if (!newRoomId) {
            alert("Please select a room first.");
            return;
        }

        console.log("Changing room for student:", selectedStudent, "to room:", newRoomId);

        $.ajax({
            url: "change_room.php",
            type: "POST",
            data: { student_id: selectedStudent, new_room_id: newRoomId },
            success: function (response) {
                let res = JSON.parse(response);
                alert(res.message);
                if (res.status === "success") location.reload();
            }
        });
    });

    $(".close").on("click", function () {
        $("#roomModal").hide();
        selectedStudent = null;
        $("#availableRoomsDropdown").hide();
        $("#changeRoomBtn").hide();
    });
});

</script>
</body>
</html>
