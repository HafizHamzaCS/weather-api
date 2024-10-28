
jQuery(document).ready(function($) {
    const forecastContainer = $('#forecast');

    if (typeof wfp_vars !== 'undefined') {
        const apiUrl = `${wfp_vars.api_url}?lat=${wfp_vars.latitude}&lon=${wfp_vars.longitude}`;
        const imagesUrl = `${wfp_vars.plugin_url}/assets/images-code.json`;
        // const ajaxUrl = 'https://www.ski-livigno.nl/wp-admin/admin-ajax.php'; // Hardcoded AJAX URL
        const ajaxUrl = window.location.origin + '/wp-admin/admin-ajax.php';


        // Load the images-code.json file
        $.getJSON(imagesUrl, function(imageCodes) {
            // Now load the weather data from the custom endpoint
            $.ajax({
                url: ajaxUrl,
                method: 'GET',
                data: {
                    action: 'get_cached_weather_data',
                    api_url: apiUrl
                },
                success: function(response) {
                    console.log(response);
                    if (!response.success) {
                        return;
                    }
                    const data = response.data.data;
                    const forecastDays = data.properties.timeseries; // Use all data points
                    const uniqueDates = new Set();
                    const forecastElements = [];
                    // Define the iconMapping object
                    const iconMapping = {
                        "clear sky": "01",
                        "fair": "02",
                        "partly cloudy": "03",
                        "cloudy": "04",
                        "light rain showers": "05",
                        "rain showers": "05",
                        "light rain showers and thunder": "05",
                        "rain showers and thunder": "05",
                        "heavy rain showers": "41",
                        "heavy rain showers and thunder": "25",
                        "light snow showers": "44",
                        "snow showers": "08",
                        "light snow showers and thunder": "28",
                        "snow showers and thunder": "21",
                        "heavy snow showers": "45",
                        "heavy snow showers and thunder": "29",
                        "heavy snow": "50",
                        "heavy snow and thunder": "34",
                        "light rain": "46",
                        "rain": "09",
                        "light rain and thunder": "30",
                        "rain and thunder": "22",
                        "heavy rain": "10",
                        "heavy rain and thunder": "11",
                        "light snow": "49",
                        "snow": "13",
                        "light snow and thunder": "33",
                        "snow and thunder": "14",
                        "fog": "15",
                        "light sleet showers": "42",
                        "sleet showers": "07",
                        "heavy sleet showers": "43",
                        "light sleet showers and thunder": "26",
                        "sleet showers and thunder": "20",
                        "heavy sleet showers and thunder": "27",
                        "light sleet": "47",
                        "sleet": "12",
                        "heavy sleet": "48",
                        "light sleet and thunder": "31",
                        "sleet and thunder": "23",
                        "heavy sleet and thunder": "32"
                    };

                    // Group data by date
                    const groupByDate = forecastDays.reduce((acc, item) => {
                        const date = new Date(item.time).toISOString().split('T')[0];
                        if (!acc[date]) acc[date] = [];
                        acc[date].push(item);
                        return acc;
                    }, {});

                    const findTemperatureInRange = (dayData, startHour, endHour) => {
                        for (let hour = startHour; hour <= endHour; hour++) {
                            const temp = dayData.find(item => new Date(item.time).getUTCHours() === hour)?.data.instant.details.air_temperature;
                            if (temp !== undefined) {
                                return Math.round(temp);
                            }
                        }
                        return null;
                    };

                    const findSymbolCodeInRange = (dayData, startHour, endHour) => {
                        for (let hour = startHour; hour <= endHour; hour++) {
                            const dataPoint = dayData.find(item => new Date(item.time).getUTCHours() === hour);
                            if (dataPoint) {
                                if (dataPoint.data.next_1_hours && dataPoint.data.next_1_hours.summary) {
                                    return dataPoint.data.next_1_hours.summary.symbol_code;
                                } else if (dataPoint.data.next_6_hours && dataPoint.data.next_6_hours.summary) {
                                    return dataPoint.data.next_6_hours.summary.symbol_code;
                                } else if (dataPoint.data.next_12_hours && dataPoint.data.next_12_hours.summary) {
                                    return dataPoint.data.next_12_hours.summary.symbol_code;
                                }
                            }
                        }
                        return 'N/A';
                    };

                    const findPrecipitationInRange = (dayData, startHour, endHour) => {
                        for (let hour = startHour; hour <= endHour; hour++) {
                            const precipitation = dayData.find(item => new Date(item.time).getUTCHours() === hour)?.data.next_6_hours?.details?.precipitation_amount;
                            if (precipitation !== undefined) {
                                return precipitation.toFixed(1); // Return precipitation in mm with 1 decimal place
                            }
                        }
                        return null;
                    };

                    const dates = Object.keys(groupByDate);

                    for (let i = 0; i < dates.length; i++) {
                    const date = dates[i];
                    const dayData = groupByDate[date];
                    const formattedDate = new Date(date).toLocaleDateString('nl-NL', {
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                    });

                    if (!uniqueDates.has(formattedDate) && uniqueDates.size < 15) {
                        uniqueDates.add(formattedDate);

                        const nextDayData = groupByDate[dates[i + 1]];

                        // Find temperatures, symbol codes, and precipitation for specific times or within a range if not available
                        const morningTemp = findTemperatureInRange(dayData, 6, 11);
                        const afternoonTemp = findTemperatureInRange(dayData, 12, 17);
                        const eveningTemp = findTemperatureInRange(dayData, 18, 23);
                        const nightTemp = nextDayData ? findTemperatureInRange(nextDayData, 0, 5) : findTemperatureInRange(dayData, 0, 5);

                        const nightSymbolCode = nextDayData ? findSymbolCodeInRange(nextDayData, 0, 5) : findSymbolCodeInRange(dayData, 0, 5);
                        const morningSymbolCode = findSymbolCodeInRange(dayData, 6, 11);
                        const afternoonSymbolCode = findSymbolCodeInRange(dayData, 12, 17);
                        const eveningSymbolCode = findSymbolCodeInRange(dayData, 18, 23);

                        const morningPrecipitation = findPrecipitationInRange(dayData, 6, 11);
                        const afternoonPrecipitation = findPrecipitationInRange(dayData, 12, 17);
                        const eveningPrecipitation = findPrecipitationInRange(dayData, 18, 23);
                        const nightPrecipitation = nextDayData ? findPrecipitationInRange(nextDayData, 0, 5) : findPrecipitationInRange(dayData, 0, 5);


                                    // Function to get image URL based on symbol code
                        function getImageUrl(symbolCode, timeOfDay) {
                            let correctedSymbolCode = symbolCode;

                            // Correct known typos in symbol codes
                            if (symbolCode === 'lightssleetshowersandthunder') {
                                correctedSymbolCode = 'lightsleetshowersandthunder';
                            } else if (symbolCode === 'lightssnowshowersandthunder') {
                                correctedSymbolCode = 'lightsnowshowersandthunder';
                            }
                            let baseCode = "";
                            for (let key in imageCodes) {
                                if (correctedSymbolCode.includes(imageCodes[key].replace(/\s/g, '').toLowerCase())) {
                                    baseCode = iconMapping[imageCodes[key].toLowerCase()];
                                    break;
                                }
                            }
                            if (baseCode) {
                                if (correctedSymbolCode.endsWith('day')) {
                                    return `${wfp_vars.plugin_url}images/${baseCode}d.svg`;
                                } else if (correctedSymbolCode.endsWith('night')) {
                                    return `${wfp_vars.plugin_url}images/${baseCode}n.svg`;
                                } else if (correctedSymbolCode.endsWith('polartwilight')) {
                                    return `${wfp_vars.plugin_url}images/${baseCode}m.svg`;
                                } else {
                                    return `${wfp_vars.plugin_url}images/${baseCode}.svg`;
                                }
                            } else {
                                // Return the default image based on time of day if no valid symbol code is found
                                return `${wfp_vars.plugin_url}images/${timeOfDay}.svg`;
                            }
                        }
                        // Function to get temperature color
                        function getTemperatureColor(temp) {
                            if (temp <= 0) {
                                return 'blue';
                            } else if (temp >= 1 && temp < 18) {
                                return 'grey';
                            } else {
                                return 'green';
                            }
                        }        
                        // Skip the entire day if only night data is available but no other time period data
                        if (morningTemp === null && afternoonTemp === null && eveningTemp === null && nightTemp !== null) {
                            continue; // Skip this day
                        }

                        // Continue with creating dayHTML
                        const dayHTML = `
                            <div class="row mt-3">
                                <div class="col-12 text-center bah-Vandaag">
                                    <span class="fw-bold fs-5 text-white">${formattedDate}</span>
                                </div>
                                ${morningTemp !== null ? `
                                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-border morning">
                                    <p>Ochtend</p>
                                    <img src="${getImageUrl(morningSymbolCode, 'morning')}" alt="${morningSymbolCode}" width="65px">
                                    <p>
                                        <span class="text-${getTemperatureColor(morningTemp)}">${morningTemp} °C</span><br>
                                        ${morningPrecipitation > 0 ? `<span class="text-blue"><img src="${wfp_vars.plugin_url}images/water.png" alt="Rain icon" width="16px"> ${morningPrecipitation} mm</span>` : ''}
                                    </p>
                                </div>
                                ` : ''}
                                ${afternoonTemp !== null ? `
                                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-border afternoon">
                                    <p>Middag</p>
                                    <img src="${getImageUrl(afternoonSymbolCode, 'afternoon')}" alt="${afternoonSymbolCode}" width="65px">
                                    <p>
                                        <span class="text-${getTemperatureColor(afternoonTemp)}">${afternoonTemp} °C</span><br>
                                        ${afternoonPrecipitation > 0 ? `<span class="text-blue"><img src="${wfp_vars.plugin_url}images/water.png" alt="Rain icon" width="16px"> ${afternoonPrecipitation} mm</span>` : ''}
                                    </p>
                                </div>
                                ` : ''}

                                ${eveningTemp !== null ? `
                                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-border evening">
                                    <p>Avond</p>
                                    <img src="${getImageUrl(eveningSymbolCode, 'evening')}" alt="${eveningSymbolCode}" width="65px">
                                    <p>
                                        <span class="text-${getTemperatureColor(eveningTemp)}">${eveningTemp} °C</span><br>
                                        ${eveningPrecipitation > 0 ? `<span class="text-blue"><img src="${wfp_vars.plugin_url}images/water.png" alt="Rain icon" width="16px"> ${eveningPrecipitation} mm</span>` : ''}
                                    </p>
                                </div>
                                ` : ''}

                                ${nightTemp !== null ? `
                                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-border night">
                                    <p>Nacht</p>
                                    <img src="${getImageUrl(nightSymbolCode, 'night')}" alt="${nightSymbolCode}" width="65px">
                                    <p>
                                        <span class="text-${getTemperatureColor(nightTemp)}">${nightTemp} °C</span><br>
                                        ${nightPrecipitation > 0 ? `<span class="text-blue"><img src="${wfp_vars.plugin_url}images/water.png" alt="Rain icon" width="16px"> ${nightPrecipitation} mm</span>` : ''}
                                    </p>
                                </div>
                                ` : ''}
                            </div>
                        `;

                        forecastElements.push(dayHTML);
                    }
                }

                const forecastHTML = `<div class="container-fluid">${forecastElements.join('')}</div>`;
                forecastContainer.html(forecastHTML);

                    // After rendering the HTML, remove the `bh-border` class for the first and second columns on mobile
                    if ($(window).width() < 769) { // Check if screen width is less than 769px (mobile view)
                        $('.row').each(function() {
                            const morningElement = $(this).find('.morning');
                            const afternoonElement = $(this).find('.afternoon');

                            if (morningElement.length) {
                                morningElement.removeClass('bh-border');
                            }
                            if (afternoonElement.length) {
                                afternoonElement.removeClass('bh-border');
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching weather data:', error);
                }
            });
        }).fail(function() {
            console.error('Error loading image codes JSON');
        });
    }
});
