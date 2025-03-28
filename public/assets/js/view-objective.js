const HTTP = new XMLHttpRequest;
const CARDS_CONTAINER = document.getElementById("cards-container");

async function objectiveMarkComplete() {
    const msg = await getWordXml("objective_marked_completed")

    Swal.fire({
        position: "top-end",
        icon: "success",
        title: msg,
        showConfirmButton: false,
        timer: 1000
    });

}
async function objectiveMarkError() {
    const msg = await getWordXml("error_marking_objective_complete")

    Swal.fire({
        position: "top-end",
        icon: "error",
        title: msg,
        showConfirmButton: false,
        timer: 1000
    });
}
function markObjectiveComplete(id) {
    var card = document.getElementById("objective-" + id);
    var url = "/CashManager/objectives/mark-completed/" + id;

    HTTP.onload = () => {
        console.log(HTTP.response)
        if (HTTP.response == 1) {
            CARDS_CONTAINER.removeChild(card);

            objectiveMarkComplete();
        }
        else {
            objectiveMarkError();
        }
    };

    HTTP.open("PUT", url);
    HTTP.send();
}