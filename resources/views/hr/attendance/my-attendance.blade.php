@extends('admin_panel.layout.app')

@section('content')
    <style>
        .my-attendance {
            max-width: 600px;
            margin: 40px auto;
        }

        .attendance-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .attendance-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 32px;
            text-align: center;
        }

        .attendance-header h2 {
            margin: 0 0 8px 0;
            font-weight: 600;
        }

        .attendance-header .time {
            font-size: 3rem;
            font-weight: 700;
            margin: 16px 0;
        }

        .attendance-header .date {
            opacity: 0.9;
        }

        .attendance-body {
            padding: 32px;
        }

        .employee-info {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .employee-avatar {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .employee-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a1a2e;
        }

        .employee-dept {
            color: #64748b;
        }

        .status-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
        }

        .status-row:not(:last-child) {
            border-bottom: 1px solid #e2e8f0;
        }

        .status-label {
            color: #64748b;
            font-size: 0.9rem;
        }

        .status-value {
            font-weight: 600;
            color: #1a1a2e;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-secondary {
            background: #f1f5f9;
            color: #64748b;
        }

        .btn-checkin {
            width: 100%;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-checkin:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
        }

        .btn-checkout {
            width: 100%;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .btn-disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }

        .btn-disabled:hover {
            transform: none;
            box-shadow: none;
        }

        .message {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .message-success {
            background: #dcfce7;
            color: #166534;
        }

        .message-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .no-employee {
            text-align: center;
            padding: 60px 20px;
        }

        .no-employee i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        /* Camera Modal */
        .camera-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .camera-modal.active {
            display: flex;
        }

        .camera-container {
            background: #1a1a2e;
            border-radius: 20px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .camera-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .camera-title.checkin {
            color: #22c55e;
        }

        .camera-title.checkout {
            color: #ef4444;
        }

        .video-container {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        #video {
            width: 100%;
            display: block;
            border-radius: 16px;
        }

        .face-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 180px;
            height: 220px;
            border: 3px dashed rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            pointer-events: none;
        }

        .camera-actions {
            display: flex;
            gap: 12px;
        }

        .btn-capture {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-capture.checkin {
            background: #22c55e;
            color: white;
        }

        .btn-capture.checkout {
            background: #ef4444;
            color: white;
        }

        .btn-cancel {
            flex: 1;
            padding: 14px;
            border: 2px solid #64748b;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            background: transparent;
            color: white;
        }

        .camera-countdown {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 5rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            display: none;
        }

        #canvas {
            display: none;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="my-attendance">
                <div class="attendance-card">
                    <div class="attendance-header">
                        <h2><i class="fa fa-fingerprint"></i> My Attendance</h2>
                        <div class="time" id="currentTime">--:--:--</div>
                        <div class="date" id="currentDate">Loading...</div>
                    </div>

                    @if ($employee)
                        <div class="attendance-body">
                            <div class="employee-info">
                                <div class="employee-avatar">
                                    {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="employee-name">{{ $employee->full_name }}</div>
                                    <div class="employee-dept">{{ $employee->department->name ?? 'N/A' }} •
                                        {{ $employee->designation->name ?? '' }}</div>
                                </div>
                            </div>

                            <div id="messageBox"></div>

                            <div class="status-box">
                                <div class="status-row">
                                    <span class="status-label">Today's Status</span>
                                    <span
                                        class="status-badge {{ $attendance ? ($attendance->status == 'present' ? 'badge-success' : ($attendance->status == 'late' ? 'badge-warning' : 'badge-secondary')) : 'badge-secondary' }}">
                                        {{ $attendance ? ucfirst($attendance->status) : 'Not Marked' }}
                                    </span>
                                </div>
                                <div class="status-row">
                                    <span class="status-label">Check In</span>
                                    <span class="status-value" id="checkInTime">
                                        {{ $attendance && $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('h:i A') : '--:--' }}
                                    </span>
                                </div>
                                <div class="status-row">
                                    <span class="status-label">Check Out</span>
                                    <span class="status-value" id="checkOutTime">
                                        {{ $attendance && $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('h:i A') : '--:--' }}
                                    </span>
                                </div>
                                <div class="status-row">
                                    <span class="status-label">Total Hours</span>
                                    <span class="status-value" id="totalHours">
                                        {{ $attendance && $attendance->total_hours ? $attendance->total_hours . ' hrs' : '--' }}
                                    </span>
                                </div>
                            </div>

                            @if (!$attendance || !$attendance->check_in_time)
                                <button type="button" class="btn-checkin" id="openCameraBtn" data-type="check_in">
                                    <i class="fa fa-camera"></i> Check In with Camera
                                </button>
                            @elseif(!$attendance->check_out_time)
                                <button type="button" class="btn-checkout" id="openCameraBtn" data-type="check_out">
                                    <i class="fa fa-camera"></i> Check Out with Camera
                                </button>
                            @else
                                <button type="button" class="btn-checkin btn-disabled" disabled>
                                    <i class="fa fa-check-circle"></i> Attendance Complete
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="attendance-body">
                            <div class="no-employee">
                                <i class="fa fa-user-slash"></i>
                                <h4>No Employee Profile</h4>
                                <p class="text-muted">Your user account is not linked to an employee profile. Please contact
                                    HR.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Camera Modal -->
    <div class="camera-modal" id="cameraModal">
        <div class="camera-container">
            <div class="camera-title" id="cameraTitle">
                <i class="fa fa-camera"></i> Check In
            </div>
            <div class="video-container">
                <video id="video" autoplay playsinline></video>
                <div class="face-guide"></div>
                <div class="camera-countdown" id="countdown"></div>
                <canvas id="canvas"></canvas>
            </div>
            <div class="camera-actions">
                <button class="btn-capture checkin" id="captureBtn">
                    <i class="fa fa-camera"></i> Capture & Check In
                </button>
                <button class="btn-cancel" id="cancelBtn">
                    <i class="fa fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        let currentType = 'check_in';
        let stream = null;
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const cameraModal = document.getElementById('cameraModal');

        // Update time
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

        // Location requirement from designation
        const requiresLocation = {{ $requiresLocation ? 'true' : 'false' }};

        // Open Camera - Check permissions first
        $('#openCameraBtn').click(async function() {
            currentType = $(this).data('type');
            const btn = $(this);

            // Show checking permissions state
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking permissions...');

            // Check location permission only if required by designation
            if (requiresLocation) {
                const locationPermission = await checkLocationPermission();
                if (!locationPermission.granted) {
                    btn.prop('disabled', false).html(currentType === 'check_in' ?
                        '<i class="fa fa-camera"></i> Check In with Camera' :
                        '<i class="fa fa-camera"></i> Check Out with Camera');
                    showPermissionError('location', locationPermission.message);
                    return;
                }
            }

            // Check camera permission
            const cameraPermission = await checkCameraPermission();
            if (!cameraPermission.granted) {
                btn.prop('disabled', false).html(currentType === 'check_in' ?
                    '<i class="fa fa-camera"></i> Check In with Camera' :
                    '<i class="fa fa-camera"></i> Check Out with Camera');
                showPermissionError('camera', cameraPermission.message);
                return;
            }

            // Restore button
            btn.prop('disabled', false).html(currentType === 'check_in' ?
                '<i class="fa fa-camera"></i> Check In with Camera' :
                '<i class="fa fa-camera"></i> Check Out with Camera');

            // Update modal appearance
            if (currentType === 'check_in') {
                $('#cameraTitle').html('<i class="fa fa-sign-in-alt"></i> Check In').removeClass('checkout')
                    .addClass('checkin');
                $('#captureBtn').html('<i class="fa fa-camera"></i> Capture & Check In').removeClass('checkout')
                    .addClass('checkin');
            } else {
                $('#cameraTitle').html('<i class="fa fa-sign-out-alt"></i> Check Out').removeClass('checkin')
                    .addClass('checkout');
                $('#captureBtn').html('<i class="fa fa-camera"></i> Capture & Check Out').removeClass('checkin')
                    .addClass('checkout');
            }

            // Start camera and open modal
            startCamera();
            cameraModal.classList.add('active');
        });

        // Check location permission
        async function checkLocationPermission() {
            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    resolve({
                        granted: false,
                        message: 'Geolocation is not supported by your browser.'
                    });
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            granted: true,
                            coords: position.coords
                        });
                    },
                    (error) => {
                        let message = 'Location permission denied.';
                        if (error.code === 1) message =
                            'Location access was denied. Please enable location permissions in your browser settings.';
                        if (error.code === 2) message =
                            'Location information is unavailable. Please check your device settings.';
                        if (error.code === 3) message = 'Location request timed out. Please try again.';
                        resolve({
                            granted: false,
                            message: message
                        });
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }

        // Check camera permission
        async function checkCameraPermission() {
            try {
                const testStream = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
                testStream.getTracks().forEach(track => track.stop()); // Stop test stream
                return {
                    granted: true
                };
            } catch (err) {
                let message = 'Camera access denied.';
                if (err.name === 'NotAllowedError') {
                    message = 'Camera permission denied. Please enable camera access in your browser settings.';
                } else if (err.name === 'NotFoundError') {
                    message = 'No camera found on your device.';
                } else if (err.name === 'NotReadableError') {
                    message = 'Camera is already in use by another application.';
                }
                return {
                    granted: false,
                    message: message
                };
            }
        }

        // Show permission error with nice UI
        function showPermissionError(type, message) {
            const icon = type === 'camera' ? 'fa-camera' : 'fa-map-marker-alt';
            const title = type === 'camera' ? 'Camera Permission Required' : 'Location Permission Required';

            $('#messageBox').html(`
                <div class="message message-error" style="text-align: center; padding: 24px;">
                    <i class="fa ${icon}" style="font-size: 2.5rem; margin-bottom: 12px; display: block;"></i>
                    <strong style="font-size: 1.1rem;">${title}</strong><br>
                    <small style="margin-top: 8px; display: block;">${message}</small>
                    <br><br>
                    <small style="color: #666;">
                        <i class="fa fa-info-circle"></i> 
                        To enable permissions, click the lock/info icon in your browser's address bar.
                    </small>
                </div>
            `);
        }

        // Start camera stream
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: 480,
                        height: 360,
                        facingMode: 'user'
                    }
                });
                video.srcObject = stream;
            } catch (err) {
                showPermissionError('camera', 'Camera access denied. Please allow camera permissions.');
                closeCamera();
            }
        }

        // Stop camera
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

        // Close modal
        function closeCamera() {
            stopCamera();
            cameraModal.classList.remove('active');
        }

        // Cancel button
        $('#cancelBtn').click(function() {
            if (!$(this).prop('disabled')) {
                closeCamera();
            }
        });

        // Disable all controls during processing
        function setProcessingState(isProcessing) {
            const captureBtn = $('#captureBtn');
            const cancelBtn = $('#cancelBtn');

            if (isProcessing) {
                captureBtn.prop('disabled', true);
                cancelBtn.prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');
            } else {
                captureBtn.prop('disabled', false);
                cancelBtn.prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
            }
        }

        // Capture photo and submit
        $('#captureBtn').click(function() {
            const btn = $(this);

            // Disable both buttons
            setProcessingState(true);
            btn.html('<i class="fa fa-spinner fa-spin"></i> Get Ready...');

            // Show countdown
            let count = 3;
            const countdownEl = $('#countdown');
            countdownEl.show().css({
                'animation': 'pulse 0.5s ease-in-out infinite'
            });

            const countdownInterval = setInterval(() => {
                countdownEl.text(count);
                count--;

                if (count === 2) btn.html('<i class="fa fa-spinner fa-spin"></i> Smile! 📸');
                if (count === 1) btn.html('<i class="fa fa-spinner fa-spin"></i> Hold still...');
                if (count === 0) btn.html('<i class="fa fa-spinner fa-spin"></i> Capturing...');

                if (count < 0) {
                    clearInterval(countdownInterval);
                    countdownEl.hide();

                    // Flash effect
                    $('.video-container').css('filter', 'brightness(2)');
                    setTimeout(() => {
                        $('.video-container').css('filter', 'brightness(1)');
                    }, 100);

                    // Capture photo
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    const photo = canvas.toDataURL('image/jpeg', 0.8);

                    btn.html('<i class="fa fa-map-marker-alt fa-pulse"></i> Getting Location...');

                    // Submit with location
                    getLocationAndSubmit(photo, btn);
                }
            }, 1000);
        });

        // Get location and submit attendance
        function getLocationAndSubmit(photo, btn) {
            // Only request location if designation requires it
            if (requiresLocation && navigator.geolocation) {
                btn.html('<i class="fa fa-map-marker-alt fa-pulse"></i> Getting Location...');
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        console.log('Location captured:', position.coords.latitude, position.coords.longitude);
                        btn.html('<i class="fa fa-cloud-upload-alt fa-pulse"></i> Uploading...');
                        submitAttendance(photo, btn, position.coords.latitude, position.coords.longitude);
                    },
                    function(error) {
                        console.log('Location error:', error.code, error.message);
                        btn.html('<i class="fa fa-cloud-upload-alt fa-pulse"></i> Uploading...');
                        submitAttendance(photo, btn, null, null);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            } else {
                // On-site worker - skip location request
                btn.html('<i class="fa fa-cloud-upload-alt fa-pulse"></i> Uploading...');
                submitAttendance(photo, btn, null, null);
            }
        }

        // Submit attendance
        function submitAttendance(photo, btn, latitude, longitude) {
            $.ajax({
                url: '{{ route('my-attendance.mark') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: currentType,
                    photo: photo,
                    latitude: latitude,
                    longitude: longitude
                },
                success: function(response) {
                    if (response.success) {
                        // Success - show in modal briefly then close
                        btn.html('<i class="fa fa-check-circle"></i> Success!').removeClass('checkin checkout')
                            .css('background', '#22c55e');
                        $('#cancelBtn').hide();

                        setTimeout(() => {
                            closeCamera();

                            let msg = response.message;
                            if (response.location) {
                                msg += '<br><small><i class="fa fa-map-marker-alt"></i> ' + response
                                    .location + '</small>';
                            }
                            $('#messageBox').html(
                                '<div class="message message-success" style="animation: fadeIn 0.3s ease;">' +
                                '<i class="fa fa-check-circle fa-lg"></i><br>' +
                                msg + '</div>');

                            // Reload after showing success
                            setTimeout(() => location.reload(), 2000);
                        }, 800);
                    } else {
                        // Error - allow retry
                        closeCamera();
                        $('#messageBox').html(
                            '<div class="message message-error"><i class="fa fa-exclamation-circle"></i> ' +
                            response.error + '</div>');
                        setProcessingState(false);
                        resetCaptureButton();
                    }
                },
                error: function() {
                    closeCamera();
                    $('#messageBox').html(
                        '<div class="message message-error"><i class="fa fa-exclamation-circle"></i> Network error. Please try again.</div>'
                    );
                    setProcessingState(false);
                    resetCaptureButton();
                }
            });
        }

        // Reset capture button text
        function resetCaptureButton() {
            const captureBtn = $('#captureBtn');
            if (currentType === 'check_in') {
                captureBtn.html('<i class="fa fa-camera"></i> Capture & Check In').addClass('checkin').removeClass(
                    'checkout');
            } else {
                captureBtn.html('<i class="fa fa-camera"></i> Capture & Check Out').addClass('checkout').removeClass(
                    'checkin');
            }
            $('#cancelBtn').show();
        }

        // Close modal on outside click (only if not processing)
        cameraModal.addEventListener('click', function(e) {
            if (e.target === cameraModal && !$('#cancelBtn').prop('disabled')) {
                closeCamera();
            }
        });

        // Keyboard escape to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && cameraModal.classList.contains('active') && !$('#cancelBtn').prop(
                    'disabled')) {
                closeCamera();
            }
        });
    </script>

    <style>
        @keyframes pulse {

            0%,
            100% {
                transform: translate(-50%, -50%) scale(1);
            }

            50% {
                transform: translate(-50%, -50%) scale(1.1);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .video-container {
            transition: filter 0.1s ease;
        }
    </style>
@endsection
