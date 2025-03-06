<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>นัดหมายใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>เลือกวันและเวลาที่ต้องการ</h1>
        <form action="process-app.php" method="POST">
            <label for="appointment-date" class="form-label">เลือกวันที่:</label>
            <input type="date" name="appointment_date" id="appointment-date" class="form-control" required>

            <label class="form-label mt-3">เลือกช่วงเวลา:</label>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#timeModal">
                เลือกช่วงเวลา
            </button>

            <!-- Hidden input เก็บค่าช่วงเวลาที่เลือก -->
            <input type="hidden" name="selected_times" id="selected-times" required>

            <button type="submit" class="btn btn-primary mt-4">บันทึก</button>
        </form>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="timeModal" tabindex="-1" aria-labelledby="timeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="timeModalLabel">เลือกช่วงเวลา</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="available-times" class="time-options"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="confirmTimes()">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const availableTimes = [
            "08:00", "08:30", "09:00", "09:30",
            "10:00", "10:30", "11:00", "11:30",
            "13:00", "13:30", "14:00", "14:30", "15:00"
        ];
        const selectedTimes = [];

        // Render times as checkbox
        function renderTimeOptions() {
            const container = document.getElementById('available-times');
            container.innerHTML = '';

            availableTimes.forEach(time => {
                const div = document.createElement('div');
                div.classList.add('form-check');

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.className = 'form-check-input';
                input.value = time;
                input.id = `time-${time}`;
                input.onchange = (e) => toggleTimeSelection(e.target);

                const label = document.createElement('label');
                label.className = 'form-check-label';
                label.htmlFor = `time-${time}`;
                label.textContent = time;

                div.appendChild(input);
                div.appendChild(label);
                container.appendChild(div);
            });
        }

        function toggleTimeSelection(checkbox) {
            if (checkbox.checked) {
                selectedTimes.push(checkbox.value);
            } else {
                const index = selectedTimes.indexOf(checkbox.value);
                if (index > -1) selectedTimes.splice(index, 1);
            }
        }

        function confirmTimes() {
            document.getElementById('selected-times').value = selectedTimes.join(',');
            alert('ช่วงเวลาที่เลือก: ' + selectedTimes.join(', '));
        }

        renderTimeOptions();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
