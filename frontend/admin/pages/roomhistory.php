<?php
include '../../../backend/dbconnection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Allocation</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../javascript/script.js?v=room-change-fix"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        .room-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            padding: 16px 0;
        }

        .room-card {
            min-height: 132px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            padding: 14px;
            cursor: pointer;
            transition: 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 12px 28px -24px rgba(15, 23, 42, 0.45);
        }

        .room-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .room-full {
            background: #ef4444 !important;
            color: white;
        }

        .room-icon {
            font-size: 30px;
            color: #3498db;
        }

        .room-number {
            font-size: 1rem;
            font-weight: bold;
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

        .room-change-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 18px;
            border-radius: 8px;
            box-shadow: 0 24px 60px -24px rgba(15, 23, 42, 0.55);
            width: min(420px, calc(100vw - 32px));
            z-index: 1002;
        }

        .room-change-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.35);
            z-index: 1001;
        }

        .room-change-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .room-change-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .close {
            cursor: pointer;
            font-size: 18px;
            color: #64748b;
        }

        #studentList {
            list-style-type: none;
            padding: 0;
            margin: 0 0 14px;
            max-height: 190px;
            overflow-y: auto;
        }

        #studentList li {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            margin: 6px 0;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            text-align: left;
        }

        .selected-student {
            background: #3498db !important;
            color: white;
        }

        #availableRoomsDropdown {
            display: none;
            margin-top: 8px;
            padding: 10px 12px;
            width: 100%;
            height: 44px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background-color: #ffffff;
            color: #1e293b;
            font-size: 14px;
            opacity: 1;
            pointer-events: auto;
            position: static;
            appearance: auto;
            background-image: none;
        }

        #changeRoomBtn {
            display: none;
            width: 100%;
            margin-top: 12px;
            padding: 8px 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        #roomChangeStatus {
            margin-top: 10px;
            min-height: 18px;
            color: #dc2626;
            font-size: 13px;
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

    <div id="roomBackdrop" class="room-change-backdrop"></div>
    <div id="roomModal" class="room-change-modal">
        <div class="room-change-header">
            <h3>Room <span id="roomNumber"></span></h3>
            <span class="close"><i class="fas fa-times"></i></span>
        </div>
        <div class="room-change-content">
            <ul id="studentList"></ul>
            <select id="availableRoomsDropdown" class="js-native-select"></select>
            <button id="changeRoomBtn">Change Room</button>
            <div id="roomChangeStatus"></div>
        </div>
    </div>
</div>

<script>
   $(document).ready(function () {
    let selectedStudent = null;
    let selectedRoom = null;

    function openRoomModal() {
        $("#roomBackdrop").show();
        $("#roomModal").show();
    }

    function closeRoomModal() {
        $("#roomBackdrop").hide();
        $("#roomModal").hide();
        selectedStudent = null;
        $("#availableRoomsDropdown").hide().html("");
        $("#changeRoomBtn").hide();
        $("#roomChangeStatus").text("");
    }

    $(".room-card").on("click", function () {
        let room_id = $(this).data("room");
        selectedRoom = room_id;
        selectedStudent = null;
        $("#availableRoomsDropdown").hide().html("");
        $("#changeRoomBtn").hide();
        $("#roomChangeStatus").text("");
        $("#studentList").html("<li data-empty=\"1\">Loading students...</li>");
        $("#roomNumber").text($(this).find(".room-number").text().replace("Room ", ""));
        openRoomModal();

        $.ajax({
            url: "fetch_students.php",
            type: "POST",
            data: { room_id: room_id },
            success: function (response) {
                $("#studentList").html(response);
            },
            error: function () {
                $("#studentList").html("<li data-empty=\"1\">Unable to load students</li>");
            }
        });
    });

    $("#studentList").on("click", "li", function () {
        if ($(this).data("empty")) {
            return;
        }

        $("#studentList li").removeClass("selected-student");
        $(this).addClass("selected-student");
        selectedStudent = $(this).data("student-id");

        $.ajax({
            url: "fetch_available_rooms.php",
            type: "GET",
            data: { current_room_id: selectedRoom },
            success: function (response) {
                let rooms = typeof response === "string" ? JSON.parse(response) : response;
                let dropdown = $("#availableRoomsDropdown");
                dropdown.html('<option value="">Select a room</option>');

                rooms.forEach(room => {
                    dropdown.append(`<option value="${room.room_id}">Room ${room.room_number} (${room.occupants}/4)</option>`);
                });

                if (rooms.length === 0) {
                    dropdown.append('<option value="" disabled>No available rooms</option>');
                }

                dropdown.show();
                $("#changeRoomBtn").show();
                $("#roomChangeStatus").text("");
            },
            error: function (xhr) {
                $("#roomChangeStatus").text("Unable to load available rooms.");
                console.error("Available rooms error:", xhr.responseText);
            }
        });
    });

    $("#changeRoomBtn").on("click", function () {
        if (!selectedStudent) {
            alert("Please select a student first.");
            return;
        }

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
                let res = typeof response === "string" ? JSON.parse(response) : response;
                alert(res.message);
                if (res.status === "success") location.reload();
            },
            error: function (xhr) {
                $("#roomChangeStatus").text("Unable to change room.");
                console.error("Change room error:", xhr.responseText);
            }
        });
    });

    $(".close, #roomBackdrop").on("click", closeRoomModal);
});

</script>
</body>
</html>
