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

                    // Group data by date
                    const groupByDate = forecastDays.reduce((acc, item) => {
                        const date = new Date(item.time).toISOString().split('T')[0]; // Format the date correctly
                        if (!acc[date]) acc[date] = [];
                        acc[date].push(item);
                        return acc;
                    }, {});

                    const findTemperatureInRange = (dayData, startHour, endHour, nextDayData) => {
                        for (let hour = startHour; hour <= endHour; hour++) {
                            const temp = dayData.find(item => new Date(item.time).getUTCHours() === hour)?.data.instant.details.air_temperature;
                            if (temp !== undefined) {
                                console.log(`Found temperature ${temp} at hour ${hour}`); // Debugging information
                                return Math.round(temp); // Round the temperature to the nearest whole number
                            }
                        }
                        if (nextDayData) {
                            for (let hour = 0; hour < 6; hour++) { // Consider up to 6 AM of the next day
                                const temp = nextDayData.find(item => new Date(item.time).getUTCHours() === hour)?.data.instant.details.air_temperature;
                                if (temp !== undefined) {
                                    console.log(`Found temperature ${temp} at hour ${hour} of next day`); // Debugging information
                                    return Math.round(temp); // Round the temperature to the nearest whole number
                                }
                            }
                        }
                        console.log(`No temperature found in range ${startHour}-${endHour}`); // Debugging information
                        return 'N/A';
                    };

                    const findSymbolCodeInRange = (dayData, startHour, endHour, nextDayData) => {
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
                        if (nextDayData) {
                            for (let hour = 0; hour < 6; hour++) { // Consider up to 6 AM of the next day
                                const dataPoint = nextDayData.find(item => new Date(item.time).getUTCHours() === hour);
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
                        }
                        console.log(`No symbol code found in range ${startHour}-${endHour}`); // Debugging information
                        return 'N/A';
                    };

                    const dates = Object.keys(groupByDate);

                    for (let i = 0; i < dates.length; i++) {
                        const date = dates[i];
                        const dayData = groupByDate[date];
                        const formattedDate = new Date(date).toLocaleDateString('nl-NL', {
                            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                        });

                        if (!uniqueDates.has(formattedDate) && uniqueDates.size < 10) {
                            uniqueDates.add(formattedDate);

                            const nextDayData = groupByDate[dates[i + 1]];

                            // Find temperatures and symbol codes for specific times or within a range if not available
                            const morningTemp = findTemperatureInRange(dayData, 6, 11);
                            const afternoonTemp = findTemperatureInRange(dayData, 12, 17);
                            const eveningTemp = findTemperatureInRange(dayData, 18, 23, nextDayData);
                            const nightTemp = findTemperatureInRange(dayData, 0, 5);

                            const nightSymbolCode = findSymbolCodeInRange(dayData, 0, 5, nextDayData);
                            const morningSymbolCode = findSymbolCodeInRange(dayData, 6, 11);
                            const afternoonSymbolCode = findSymbolCodeInRange(dayData, 12, 17);
                            const eveningSymbolCode = findSymbolCodeInRange(dayData, 18, 23, nextDayData);

                            console.log(`Date: ${formattedDate}, Night Temp: ${nightTemp}, Morning Temp: ${morningTemp}, Afternoon Temp: ${afternoonTemp}, Evening Temp: ${eveningTemp}`);
                            console.log(`Night Symbol: ${nightSymbolCode}, Morning Symbol: ${morningSymbolCode}, Afternoon Symbol: ${afternoonSymbolCode}, Evening Symbol: ${eveningSymbolCode}`);

                            // Mapping of weather conditions to icon base codes
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

                            // Function to get image URL based on symbol code
                            function getImageUrl(symbolCode) {
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

                                if (correctedSymbolCode.endsWith('day')) {
                                    return `${wfp_vars.plugin_url}images/${baseCode}d.svg`;
                                } else if (correctedSymbolCode.endsWith('night')) {
                                    return `${wfp_vars.plugin_url}images/${baseCode}n.svg`;
                                } else if (correctedSymbolCode.endsWith('polartwilight')) {
                                    return `${wfp_vars.plugin_url}images/${baseCode}m.svg`;
                                } else {
                                    return `${wfp_vars.plugin_url}images/${baseCode}.svg`;
                                }
                            }

                            // Function to get temperature color
                            function getTemperatureColor(temp) {
                                if (temp < 3) {
                                    return 'blue';
                                } else if (temp >= 3 && temp <= 15) {
                                    return 'grey';
                                } else {
                                    return 'orange';
                                }
                            }

                            // Construct the HTML for the day
                            const dayHTML = `
                                <div class="row mt-3">
                                    <div class="col-12 text-center bah-Vandaag">
                                        <span class="fw-bold fs-5 text-white"> ${formattedDate}</span>
                                    </div>
                                    
                                    <div class="col-6 col-md-3 col-sm-6 text-center mt-3 border-end border-dark">
                                        <p>Ochtend</p>
                                        <img src="${getImageUrl(morningSymbolCode)}" alt="${morningSymbolCode}" width="65px">
                                        <p>
                                            <span class="text-${getTemperatureColor(morningTemp)}">${morningTemp} °C</span><br>
                                        </p>
                                    </div>
                                    <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-col-bdr">
                                        <p>Middag</p>
                                        <img src="${getImageUrl(afternoonSymbolCode)}" alt="${afternoonSymbolCode}" width="65px">
                                        <p>
                                            <span class="text-${getTemperatureColor(afternoonTemp)}">${afternoonTemp} °C</span><br>
                                        </p>
                                    </div>
                                    <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-col-bdr2 border-end border-dark">
                                        <p>Avond</p>
                                        <img src="${getImageUrl(eveningSymbolCode)}" alt="${eveningSymbolCode}" width="65px">
                                        <p>
                                            <span class="text-${getTemperatureColor(eveningTemp)}">${eveningTemp} °C</span><br>
                                        </p>
                                    </div>
                                    <div class="col-6 col-md-3 col-sm-6 text-center mt-3 ">
                                        <p>S nachts</p>
                                        <img src="${getImageUrl(nightSymbolCode)}" alt="${nightSymbolCode}" width="65px">
                                        <p>
                                            <span class="text-${getTemperatureColor(nightTemp)}">${nightTemp} °C</span><br>
                                        </p>
                                    </div>
                                </div>
                            `;

                            forecastElements.push(dayHTML);
                        }
                    }

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
