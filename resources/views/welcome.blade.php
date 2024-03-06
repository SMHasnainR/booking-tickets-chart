<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circle Graph with Empty Seats</title>
    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css')  }}">

</head>
<body>
<div class="main-container">
    <div class="heading">
        <h1>
            Seating Chart
        </h1>
        <div class="btn-wrapper">
            <button id="saveAndPrintBtn" class="button --shine" onclick="saveAndPrint()">Save & Print</button>
        </div>
    </div>

    <div class="ticket-container">
        <section id="tickets-chart-right" class="tickets-chart">

        </section>
        <section id="tickets-chart-left" class="tickets-chart">

        </section>
    </div>
    <hr>
    <div id="label-container">

    </div>
</div>

{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>--}}
{{--<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.es.min.js"></script>--}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>

<script>
    window.jsPDF = window.jspdf.jsPDF;

    var i;

    // already booked seat in the database
    const bookedSeats = @json($bookedSeats) ;
    let unsavedBookedSeats = [];

    let colorPriceScheme = [
        {
            index: [] ,
            sideIndex: [67, 66, 65] ,
            color: 'rgba(38, 94, 69, 1)', // light Green
            textColor: '#EEEEEE',
            name: 'VVIP - OV',
            price: 35
        },
        {
            index: [] ,
            sideIndex: [70, 69, 68] ,
            color: 'rgba(37, 40, 213, 1)', // light Blue
            textColor: '#EEEEEE',
            name: 'VIP - OV',
            price: 25
        },
        {
            index: [83, 82] ,
            sideIndex: [83, 82, 81, 80, 79, 78, 77, 76, 75, 74] ,
            color: 'rgba(98, 20, 140, 1)', // Purple
            textColor: '#EEEEEE',
            name: 'GA',
            price: 15
        },
        {
            index: [81, 80, 79, 78, 77],
            sideIndex: [73, 72, 71] ,
            color: 'rgba(241, 193, 33, 1)', // Yellow
            textColor: 'black',
            name: 'GA',
            price: 20
        },
        {
            index: [76, 75, 74],
            sideIndex: [] ,
            color: 'rgba(201, 0, 192, 1)', // Pink
            textColor: '#EEEEEE',
            name: 'GA',
            price: 30
        },
        {
            index: [73, 72, 71],
            sideIndex: [],
            color: 'rgba(210, 127, 57, 1)',  // Orange
            textColor: 'black',
            name: 'GA',
            price: 40
        },
        {
            index: [70, 69, 68],
            sideIndex: [],
            color: 'rgba(148, 186, 235, 1)', // Blue
            textColor: 'black',
            name: 'VIP',
            price: 50
        },
        {
            index: [67, 66, 65],
            sideIndex: [],
            color: 'rgba(134, 197, 108, 1)',  // Green
            textColor: 'black',
            name: 'VVIP',
            price: 75
        },
    ];
    let ticketsChart;
    let rowStartAt;
    let rowEndAt;
    let rowLenght;
    let initialPadding = 5;
    let initialWidth = 25;
    let alphabetIndex = 83;

    let row, counter, increasePadRight, increasePad, initialDegree, initialTop, initialRight;
    row = counter = increasePadRight = increasePad = initialDegree = initialTop = initialRight = 0;

    // right is left chart
    makeChart('tickets-chart-right', 36, 19, 33);

    // left is right chart
    makeChart('tickets-chart-left', 18, 1, 5);

    function makeChart(chart, rowStart, rowEnd, spacePoint = 0) {
        valueReset();

        rowStartAt = rowStart;
        rowEndAt = rowEnd;
        rowLenght = (rowStartAt - rowEndAt) + 1;

        ticketsChart = document.getElementById(chart);

        let seatId; // will store seat value like A-11
        for (i = rowStartAt; i => rowEndAt; i--) {
            seatId = String.fromCharCode(alphabetIndex)+'-'+i;

            // Add Alphabet in chart ( on left side )
            if (i === 36) {
                ticketsChart.innerHTML += `
                <div class="seat-container">
                    <div class="alphabet"
                    style=" padding: ${initialPadding}px 0px;
                        top: ${initialTop}px;
                        width: ${initialWidth}px;
                        transform: rotate(${initialDegree}deg);
                        right: ${initialRight + 30}px;
                        ">
                        ${String.fromCharCode(alphabetIndex)}
                    </div>
                </div>
                `;
            }

            // If seat are from side then SideSeat = true
            let sideSeat = false
            if( [36,35,34,33,4,3,2,1].includes(i) ){
                sideSeat = true;
            }

            // Append the new seat HTML to the 'tickets-chart' element
            ticketsChart.innerHTML += `
            <div class="seat-container">
                <div class="seat ${bookedSeats.includes(seatId) ? 'purchased' : 'empty'}"
                    data-id="${seatId}"
                    style=" padding: ${initialPadding}px 0px;
                        top: ${initialTop}px;
                        width: ${initialWidth}px;
                        transform: rotate(${Math.min(initialDegree, 18)}deg);
                        right: ${initialRight}px;
                        background: ${ sideSeat ? findObjectBySideIndex(alphabetIndex).color : findObjectByIndex(alphabetIndex).color };
                        font-weight: bolder;
                        color: ${ sideSeat ? findObjectBySideIndex(alphabetIndex).textColor : findObjectByIndex(alphabetIndex).textColor };
                        border: 1px solid ${findObjectBySideIndex(alphabetIndex).color};
                    ">
                    ${i}
                </div>
            </div>
            `;

            // Add Alphabet in the middle of  chart
            if (i === 19) {
                ticketsChart.innerHTML += `
                <div class="seat-container">
                    <div class="alphabet"
                    style=" padding: ${initialPadding}px 0px;
                        top: ${initialTop + 30}px;
                        width: ${initialWidth}px;
                        transform: rotate(${Math.min(initialDegree, 18)}deg);
                        right: ${initialRight - 40}px;
                        ">
                        ${String.fromCharCode(alphabetIndex)}
                    </div>
                </div>
                `;
            }

            // Add Alphabet in chart ( on right side )
            if (i === 1) {
                ticketsChart.innerHTML += `
                <div class="seat-container">
                    <div class="alphabet"
                        style=" padding: ${initialPadding}px 0px;
                        top: ${initialTop + 25}px;
                        width: ${initialWidth}px;
                        transform: rotate(${initialDegree}deg);
                        right: ${initialRight - 30}px;
                        ">
                        ${String.fromCharCode(alphabetIndex)}
                    </div>
                </div>
                `;
            }

            if (i === spacePoint) {
                ticketsChart.innerHTML += `
                <div class="seat-container">
                    <div class="">
                    </div>
                </div>
            `;
            }

            // Add a line break for ending the row
            if (i === rowEndAt) {
                nextRow();
            }

            nextColoumn();

            if (row === 19) {
                break;
            }
        }
    }

    function nextColoumn() {
        initialDegree += 1.2;

        // deflecting the starting rows towards down
        if (row < 17 / 8) {
            initialTop *= 1.038;
        } else if (row < 17 / 4) {
            initialTop *= 1.028;
        } else if (row < 17 / 2) {
            initialTop *= 1.018;
        }
        initialTop += ((increasePad / 2));

        initialRight += (1 + increasePadRight / 1.2);
        increasePad++;
    }

    function nextRow() {
        ticketsChart.innerHTML += '<br>';
        initialPadding -= 1 / 6;
        initialWidth -= 1 / 2;
        initialDegree = 0;
        initialTop = 0;
        initialRight = 0;
        increasePad -= (rowLenght);
        increasePadRight += increasePad + 1.7;

        i = (rowStartAt + 1);

        // increase row counter
        row++;

        // decrease alphabet index
        alphabetIndex--;
    }

    function valueReset() {
        alphabetIndex = 83;
        initialPadding = 5;
        initialWidth = 25;
        row = counter = increasePadRight = increasePad = initialDegree = initialTop = initialRight = 0;
    }

    function findObjectByIndex(indexToFind) {
        for (let i = 0; i < colorPriceScheme.length; i++) {
            if (colorPriceScheme[i].index.includes(indexToFind)) {
                return colorPriceScheme[i];
            }
        }
        return null; // Return null if the index is not found in any object
    }

    function findObjectBySideIndex(indexToFind) {
        for (let i = 0; i < colorPriceScheme.length; i++) {
            if (colorPriceScheme[i]?.sideIndex.includes(indexToFind)) {
                return colorPriceScheme[i];
            }
        }
        return null; // Return null if the index is not found in any object
    }

    // Function to create and display labels
    function displayLabels() {
        const labelContainer = document.getElementById('label-container');

        colorPriceScheme.reverse(); // Reverse the array if needed
        labelContainer.innerHTML = ''; // Clear the container before adding new content

        colorPriceScheme.forEach((labelData, index) => {

            labelContainer.innerHTML += `
            <div class="label-wrapper">
                <div class="label" style="background: ${labelData.color}; color: ${labelData.textColor}">
                    Price: $${labelData.price}
                </div>
                <div class="name"> ${labelData.name ?? '' } </div>
                <div class="char-code">
                    (${String.fromCharCode(labelData.index[0])} - ${String.fromCharCode(labelData.index[labelData.index.length - 1])})
                </div>
            </div>
        `;

        });
    }

    // Call the function to display labels
    displayLabels();

    // Usign Jqury
    $(function () {
        let id;
        let passcode = "{{ config('app.seat_passcode') }}";

        // Add click event to elements with class 'seat'
        $("body").on('click', '.seat.empty', function () {

            // Show an alert with the text content of the clicked seat
            let seat_number = $(this).data('id');
            Swal.fire({
                title: `Enter passcode to book ${seat_number} seat?`,
                input: "text",
                inputAttributes: {
                    autocapitalize: "off"
                },
                showCancelButton: true,
                confirmButtonText: "Book it",
                showLoaderOnConfirm: true,
                preConfirm: async (login) => {
                    try {

                        // Checking Passcode
                        if(!(login == passcode)) {
                            throw new Error('Wrong Passcode enter!')
                        }

                        // appending seat in unsavedBookedSeats array
                        unsavedBookedSeats.push(seat_number)

                        // Marking seat as unsaved booked
                        $(this).addClass('purchased unsaved')
                        $(this).removeClass('empty')

                        // Poping Success Message
                        Swal.fire({
                            title: "Booked!",
                            text: "Your seat has been booked.",
                            icon: "success"
                        });

                    } catch (error) {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    }
                },
            })

        });


    });

    async function saveAndPrint() {
        const saveAndPrintBtn = document.getElementById('saveAndPrintBtn');

        try {
            // Add loader to saveAndPrint Button
            saveAndPrintBtn.innerText = 'Saving...';
            saveAndPrintBtn.disabled = true;

            // check if there are any unsaved seats in unsavedBookedSeats
            if(unsavedBookedSeats.length === 0){
                throw new Error("You does not book any seat!");
            }

            // Save seats to the database
            const saveResponse = await fetch("{{ route('book-seat') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '@csrf',   // Replace with the actual CSRF token if needed
                },
                body: JSON.stringify({ booked_seats: unsavedBookedSeats }),
            });

            // Check for any errors
            if (!saveResponse.ok) {
                console.error(`Error: ${saveResponse.status} - ${saveResponse.statusText}`);
                return;
            }

            // Generate PDF
            const pdf = new jsPDF();
            pdf.text('Seat Numbers:', 10, 10);
            unsavedBookedSeats.forEach((seat, index) => {
                pdf.text(seat, 10, 20 + (index * 10));
            });
            pdf.save('seat_numbers.pdf');
            pdf.autoPrint();
            window.open(pdf.output('bloburl'), '_blank');

            // removed unsaved class from unsavedBookedSeats's seat
            unsavedBookedSeats.forEach((seat, index) => {
                let seatElement = document.querySelector(`[data-id=${seat}]`);
                seatElement.classList.remove('unsaved');
            });

            // Empty the unsavedBookedSeats array
            unsavedBookedSeats = [];

            // Remove Loader from saveAndPrint Button
            saveAndPrintBtn.innerText = 'Save and Print';
            saveAndPrintBtn.disabled = false;

        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: error,
            });

            // Remove Loader and reset button text on error
            saveAndPrintBtn.innerText = 'Save and Print';
            saveAndPrintBtn.disabled = false;
        }
    }

</script>

</body>
</html>
