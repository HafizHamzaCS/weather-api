jQuery(document).ready(function($) {
    if (typeof wfp_vars !== 'undefined') {
        const apiUrl = `${wfp_vars.api_url}?lat=${wfp_vars.latitude}&lon=${wfp_vars.longitude}`;
        const imagesUrl = `${wfp_vars.plugin_url}/assets/images-code.json`;

        // Load the images-code.json file
        $.getJSON(imagesUrl, function(imageCodes) {
            // Now load the weather data from the Yr API
            $.ajax({
                url: apiUrl,
                method: 'GET',
                success: function(data) {
                    console.log("Full API Response:", data);
                    const forecastContainer = $('#forecast');
                    const forecastDays = data.properties.timeseries; // Use all data points
                    const uniqueDates = new Set();
                    const forecastElements = [];
                    forecastDays.forEach(day => {
                        const date = new Date(day.time);
                        const formattedDate = date.toLocaleDateString('en-GB', {
                            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                        });
                        if (!uniqueDates.has(formattedDate) && uniqueDates.size < 7) { // Limit to the next 7 days
                            uniqueDates.add(formattedDate);

                            const details = day.data.instant.details;
                            const next1HoursSummary = day.data.next_1_hours ? day.data.next_1_hours.summary.symbol_code : 'N/A';
                            const next6HoursSummary = day.data.next_6_hours ? day.data.next_6_hours.summary.symbol_code : 'N/A';
                            const next12HoursSummary = day.data.next_12_hours ? day.data.next_12_hours.summary.symbol_code : 'N/A';

                            // Function to get image URL based on symbol code
                            function getImageUrl(symbolCode) {
                                let baseCode = "";
                                for (let key in imageCodes) {
                                    if (symbolCode.includes(imageCodes[key].replace(/\s/g, '').toLowerCase())) {
                                        baseCode = key;
                                        break;
                                    }
                                }
                                if (symbolCode.endsWith('day')) {
                                    return `${wfp_vars.plugin_url}images/${baseCode}d.svg`;
                                } else if (symbolCode.endsWith('night')) {
                                    return `${wfp_vars.plugin_url}images/${baseCode}n.svg`;
                                } else if (symbolCode.endsWith('polartwilight')) {
                                    return `${wfp_vars.plugin_url}images/${baseCode}m.svg`;
                                } else {
                                    return `${wfp_vars.plugin_url}images/${baseCode}.svg`;
                                }
                            }


                            // Construct the HTML for the day
                            const dayHTML = `
                                <div class="row mt-3">
                                    <div class="col-12 text-center bah-Vandaag">
                                        <span class="fw-bold fs-5 text-white">Vandaag ${formattedDate}</span>
                                    </div>
                                    <div class="col-6 col-md-3 col-sm-6 text-center mt-3 border-end border-dark">
                                        <p>Ochtend</p>
                                        <img src="${getImageUrl(next1HoursSummary)}"alt="${next1HoursSummary}"  width="65px">
                                        <p>
                                            <span class="text-danger">${details.air_temperature} °C</span><br>
                                        </p>
                                        <p>
                                            <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                                        </p>
                                    </div>
                                    <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-col-bdr">
                                        <p>Middag</p>
                                        <img src="${getImageUrl(next6HoursSummary)}" alt="${next6HoursSummary}" width="65px">
                                        <p>
                                            <span class="text-danger">${details.air_temperature} °C</span><br>
                                        </p>
                                        <p>
                                            <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                                        </p>
                                    </div>
                                    <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-col-bdr2 border-end border-dark">
                                        <p>Avond</p>
                                        <img src="${getImageUrl(next12HoursSummary)}" alt="${next12HoursSummary}" width="65px">
                                        <p>
                                            <span class="text-danger">${details.air_temperature} °C</span><br>
                                        </p>
                                        <p>
                                            <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                                        </p>
                                    </div>
                                </div>
                            `;

                            forecastElements.push(dayHTML);
                        }   
                    });

                    const forecastHTML = `<div class="container-fluid">${forecastElements.join('')}</div>`;
                    forecastContainer.html(forecastHTML);
                },
                error: function(error) {
                    console.error('Error fetching weather data:', error);
                }
            });
        }).fail(function() {
            console.error('Error loading image codes JSON');
        });
    }
});
    