addLoadEvent( () => {
    let reminderForm = document.querySelector("#create_reminder_form");
    if(reminderForm){
        reminderForm.addEventListener("submit", event => {
            let lens = reminderForm.querySelector("#form-field-lens").value;
            let firstUsage = reminderForm.querySelector("#form-field-first_usage").value;
            let email = reminderForm.querySelector("#form-field-email").value;
            
            let data = {
                lens: lens,
                firstUsage: firstUsage,
                email: email
            }
    
            let nonce = document.querySelector("meta[name='nonce']");
            let loggedIn = document.querySelector("body.logged-in") ? true : false;
            let urlSuffix = loggedIn ? "?_wpnonce=" + nonce.content : "";
            let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/reminders/create-new-reminder" + urlSuffix;
    
            let args = {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            };
            
            fetch(url, args)
                .then(response => response.json())
                .then(result => {
                    console.log(result);
                });
        });
    }

    let deleteReminderBtns = document.querySelectorAll(".delete-reminder .elementor-button");
    if(deleteReminderBtns){
        deleteReminderBtns.forEach(deleteReminder => {
            deleteReminder.addEventListener("click", () => {
                let reminderGrid = document.querySelector(".page-id-9 .ecs-posts");
                reminderGrid.style.opacity = 0.5;
                console.log(deleteReminder.id);
                let data = {
                    reminder_id: deleteReminder.id
                };

                let args = {
                    method: "DELETE",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                };

                let nonce = document.querySelector("meta[name='nonce']");
                let loggedIn = document.querySelector("body.logged-in") ? true : false;
                let urlSuffix = loggedIn ? "?_wpnonce=" + nonce.content : "";
                let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/reminders/delete-reminder" + urlSuffix;

                fetch(url, args)
                .then(response => response.json())
                .then(result => {
                    if(result.code == 200){
                        deleteReminder.closest("article.reminder").style.display = "none";
                        alert("Podsjetnik uspješno izbrisan");
                    }else{
                        alert("Greška pri brisanju podsjetnika", "#f90404" );
                    }
                    reminderGrid.style.opacity = 1;
                });
            });
        });
    }
});