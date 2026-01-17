<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Attendance Kiosk - Face Recognition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            color: white;
            font-family: 'Segoe UI', sans-serif;
        }

        .kiosk-container {
            padding: 20px;
        }

        .time-display {
            font-size: 4rem;
            font-weight: 300;
            text-align: center;
            margin-bottom: 10px;
        }

        .date-display {
            font-size: 1.5rem;
            text-align: center;
            color: #aaa;
            margin-bottom: 30px;
        }

        .camera-container {
            background: #000;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            max-width: 640px;
            margin: 0 auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        #video,
        #capturedImage {
            width: 100%;
            display: block;
        }

        #capturedImage {
            display: none;
        }

        .camera-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .face-circle {
            width: 250px;
            height: 300px;
            border: 4px dashed rgba(255, 255, 255, 0.5);
            border-radius: 50%;
        }

        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }

        .btn-check-in {
            background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
            border: none;
            font-size: 1.5rem;
            padding: 20px 60px;
            border-radius: 50px;
            margin: 10px;
        }

        .btn-check-out {
            background: linear-gradient(135deg, #e17055 0%, #d63031 100%);
            border: none;
            font-size: 1.5rem;
            padding: 20px 60px;
            border-radius: 50px;
            margin: 10px;
        }

        .status-message {
            text-align: center;
            margin-top: 20px;
            font-size: 1.3rem;
        }

        .employee-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }

        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
        }

        #loading {
            display: none;
            text-align: center;
            margin-top: 20px;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>

<body>
    <a href="{{ route('hr.attendance.index') }}" class="back-link">
        <i class="fa fa-arrow-left"></i> Back to Attendance
    </a>

    <div class="container kiosk-container">
        <div class="time-display" id="currentTime">--:--:--</div>
        <div class="date-display" id="currentDate">Loading...</div>

        <div class="camera-container">
            <video id="video" autoplay playsinline></video>
            <img id="capturedImage" alt="Captured">
            <canvas id="canvas" style="display: none;"></canvas>
            <div class="camera-overlay">
                <div class="face-circle"></div>
            </div>
        </div>

        <div class="action-buttons">
            <button class="btn btn-check-in text-white" id="checkInBtn">
                <i class="fa fa-sign-in-alt"></i> Check In
            </button>
            <button class="btn btn-check-out text-white" id="checkOutBtn">
                <i class="fa fa-sign-out-alt"></i> Check Out
            </button>
        </div>

        <div id="loading">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Processing...</span>
            </div>
            <p class="mt-2">Processing attendance...</p>
        </div>

        <div class="status-message" id="statusMessage"></div>

        <div class="employee-info" id="employeeInfo">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img id="employeePhoto" src="" class="rounded-circle" width="80" height="80"
                        style="object-fit: cover;">
                </div>
                <div class="col">
                    <h4 id="employeeName" class="mb-1"></h4>
                    <p id="employeeDept" class="mb-0 text-muted"></p>
                </div>
                <div class="col-auto">
                    <span id="attendanceStatus" class="badge bg-success fs-5"></span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Update time display
        function updateTime() {
            const now = new Date();
            const time = now.toLocaleTimeString('en-US', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const date = now.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            $('#currentTime').text(time);
            $('#currentDate').text(date);
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Camera setup
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const capturedImage = document.getElementById('capturedImage');
        let stream = null;

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: 640,
                        height: 480,
                        facingMode: 'user'
                    }
                });
                video.srcObject = stream;
            } catch (err) {
                console.error('Camera error:', err);
                $('#statusMessage').html(
                    '<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Camera access denied. Please allow camera access.</span>'
                    );
            }
        }
        startCamera();

        // Capture photo
        function capturePhoto() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            return canvas.toDataURL('image/jpeg', 0.8);
        }

        // Mark attendance
        function markAttendance(type) {
            const photo = capturePhoto();

            // Show captured image
            capturedImage.src = photo;
            capturedImage.style.display = 'block';
            video.style.display = 'none';

            $('#loading').show();
            $('#statusMessage').text('');
            $('.action-buttons button').prop('disabled', true);

            $.ajax({
                url: '{{ route('hr.attendance.mark') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: type,
                    photo: photo
                },
                success: function(response) {
                    $('#loading').hide();

                    if (response.success) {
                        if (response.employee) {
                            $('#employeeName').text(response.employee.name);
                            $('#employeeDept').text(response.employee.department);
                            if (response.employee.photo) {
                                $('#employeePhoto').attr('src', response.employee.photo);
                            } else {
                                $('#employeePhoto').attr('src', 'https://ui-avatars.com/api/?name=' + response
                                    .employee.name + '&background=random');
                            }
                            $('#attendanceStatus').text(type === 'check_in' ? 'Checked In' : 'Checked Out')
                                .removeClass('bg-success bg-danger').addClass(type === 'check_in' ?
                                    'bg-success' : 'bg-danger');
                            $('#employeeInfo').slideDown();
                        }

                        let msg = '<span class="text-success"><i class="fa fa-check-circle"></i> ' + response
                            .message + '</span>';
                        if (response.is_late) {
                            msg += '<br><span class="text-warning"><i class="fa fa-clock"></i> Late by ' +
                                response.late_minutes + ' minutes</span>';
                        }
                        $('#statusMessage').html(msg);
                    } else {
                        $('#statusMessage').html(
                            '<span class="text-danger"><i class="fa fa-times-circle"></i> ' + response
                            .error + '</span>');
                    }

                    // Reset after 5 seconds
                    setTimeout(resetKiosk, 5000);
                },
                error: function(xhr) {
                    $('#loading').hide();
                    $('#statusMessage').html(
                        '<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Error processing attendance</span>'
                        );
                    setTimeout(resetKiosk, 3000);
                }
            });
        }

        function resetKiosk() {
            video.style.display = 'block';
            capturedImage.style.display = 'none';
            $('#employeeInfo').slideUp();
            $('#statusMessage').text('');
            $('.action-buttons button').prop('disabled', false);
        }

        // Button handlers
        $('#checkInBtn').click(function() {
            markAttendance('check_in');
        });

        $('#checkOutBtn').click(function() {
            markAttendance('check_out');
        });
    </script>
</body>

</html>
