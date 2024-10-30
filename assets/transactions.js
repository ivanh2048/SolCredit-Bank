$(document).ready(function() {
    $("#settingsButton").click(function() {
        $("#settingsModal").css("display", "block");
    });

    $("#closeModal").click(function() {
        $("#settingsModal").css("display", "none");
    });

    $(window).click(function(event) {
        if ($(event.target).is("#settingsModal")) {
            $("#settingsModal").css("display", "none");
        }
    });

    $("#changePinButton").click(function() {
        var newPin = prompt("Wprowadź nowy PIN (4 cyfry):");
        if (newPin && newPin.length === 4 && !isNaN(newPin)) {
            $.ajax({
                url: '/transactions/api.php',
                type: 'POST',
                data: { action: 'change_pin', new_pin: newPin },
                success: function(response) {
                    var result = JSON.parse(response); 
                    alert(result.message || "Operacja zakończona pomyślnie");
                },
                error: function() {
                    alert('Wystąpił błąd podczas zmiany PIN.');
                }
            });
        } else {
            alert("Nieprawidłowy PIN. Spróbuj ponownie.");
        }
    });

    $("#toggleCardLockButton").click(function() {
        var currentText = $(this).text();
        var lockStatus = (currentText === "Zablokuj") ? "lock" : "unlock";
        var button = $(this); 

        $.ajax({
            url: '/transactions/api.php',
            type: 'POST',
            data: { action: 'toggle_card_lock', lock_status: lockStatus },
            success: function(response) {
                var result = JSON.parse(response); 
                alert(result.message || "Status karty został zmieniony");
                button.text((lockStatus === "lock") ? "Odblokuj" : "Zablokuj");
            },
            error: function() {
                alert('Wystąpił błąd podczas zmiany statusu karty.');
            }
        });
    });

    $("#setTransactionLimitsButton").click(function() {
        var newLimit = prompt("Wprowadź nowy limit transakcji (PLN):");
        if (newLimit && !isNaN(newLimit)) {
            $.ajax({
                url: '/transactions/api.php',
                type: 'POST',
                data: { action: 'set_transaction_limit', new_limit: newLimit },
                success: function(response) {
                    var result = JSON.parse(response); 
                    alert(result.message || "Limit transakcji został ustawiony pomyślnie");
                },
                error: function() {
                    alert('Wystąpił błąd podczas ustawiania limitu.');
                }
            });
        } else {
            alert("Nieprawidłowy limit. Spróbuj ponownie.");
        }
    });
});
