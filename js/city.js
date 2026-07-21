$(document).ready(function () {
    // 1. Declare references to DOM components as constants
    const addNewButton = $("#add_new_city");
    const cityDetailsModal = $("#city_details_model");
    const saveCityButton = $("#save_city");
    const filterCountryDropdown = $("#filter_country");
    const modalCountryDropdown = $("#city_details_country");
    
    const inputFields = $("input");
    const selectFields = "select";

    // 2. Global application function to fetch countries via the REST API
    function getCountries(targetDropdown, defaultOption) {
        $.getJSON("controllers/country_operations.php", { get_countries: true })
            .done(function (data) {
                let dropdownHtml = "";
                if (defaultOption === "all") {
                    dropdownHtml = `<option value="0">&lt; All Countries &gt;</option>`;
                } else {
                    dropdownHtml = `<option value="">&lt; Choose Country &gt;</option>`;
                }

                // Loop cleanly through returned database array objects
                $.each(data, function (index, country) {
                    dropdownHtml += `<option value="${country.country_id}">${country.country_name}</option>`;
                });

                targetDropdown.html(dropdownHtml);
            })
            .fail(function (xhr, status, error) {
                $("#filter_city_notifications").html(
                    `<div class="alert alert-danger py-1 small"><strong>Error:</strong> Failed to fetch regional frames (${xhr.statusText})</div>`
                );
            });
    }

    // 3. Helper function to check if a string is a valid JSON object
    function isJSON(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    // 4. Fire initialization lookups when the view finishes loading
    getCountries(filterCountryDropdown, "all");
    getCountries(modalCountryDropdown, "choose");

    // 5. Wire up the add button interaction to trigger the modal
    addNewButton.on("click", function () {
        $("#city_id").val("0");
        $("#city_details_city_name").val("");
        $("#city_detail_notifications").html("");
        cityDetailsModal.modal("show");
    });

    // 6. Clear notification banners when users fix inputs
    inputFields.on("input", function () {
        $("#city_detail_notifications").html("");
    });
    $(document).on("change", selectFields, function () {
        $("#city_detail_notifications").html("");
    });

    // 7. Handle AJAX form submission tracking logic
    saveCityButton.on("click", function () {
        const cityId = $("#city_id").val();
        const countryId = modalCountryDropdown.val();
        const cityName = $("#city_details_city_name").val().trim();

        // Validate that fields are populated before sending
        if (countryId === "") {
            $("#city_detail_notifications").html(
                `<div class="alert alert-info py-1 small"><i class="fas fa-info-circle mr-1"></i>Please select a country first.</div>`
            );
            modalCountryDropdown.focus();
            return;
        }
        if (cityName === "") {
            $("#city_detail_notifications").html(
                `<div class="alert alert-info py-1 small"><i class="fas fa-info-circle mr-1"></i>Please provide a city name first.</div>`
            );
            $("#city_details_city_name").focus();
            return;
        }

        // POST transaction block
        $.post("controllers/city_operations.php", {
            save_city: true,
            city_id: cityId,
            city_name: cityName,
            country_id: countryId
        }, function (rawResponse) {
            let processedData = rawResponse;
            
            if (isJSON(rawResponse)) {
                processedData = JSON.parse(rawResponse);
            }

            if (processedData.status === "success") {
                $("#city_detail_notifications").html(
                    `<div class="alert alert-success py-1 small"><i class="fas fa-check-circle mr-1"></i>The city was saved successfully!</div>`
                );
                $("#city_details_city_name").val("").focus();
            } else if (processedData.status === "exists") {
                $("#city_detail_notifications").html(
                    `<div class="alert alert-warning py-1 small"><i class="fas fa-exclamation-triangle mr-1"></i>City name already exists in this country.</div>`
                );
                $("#city_details_city_name").focus();
            } else {
                $("#city_detail_notifications").html(
                    `<div class="alert alert-danger py-1 small"><i class="fas fa-times-circle mr-1"></i>An error occurred while writing to the server.</div>`
                );
            }
        });
    });
});