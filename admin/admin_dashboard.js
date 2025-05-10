
document.getElementById("toggleSidebar").addEventListener("click", function () {
    document.getElementById("sidebarMenu").classList.toggle("active");
});

document.getElementById("darkToggle").addEventListener("click", function () {
    document.body.classList.toggle("dark-mode");
});

var ctx1 = document.getElementById("studentChart").getContext("2d");
var studentChart = new Chart(ctx1, {
    type: "doughnut",
    data: {
        labels: ["Total Students", "Total Courses", "Total Exams"],
        datasets: [{
            data: [total_students, total_courses, total_exams],
            backgroundColor: ["#007bff", "#28a745", "#ffc107"]
        }]
    }
});

var ctx2 = document.getElementById("feesChart").getContext("2d");
var feesChart = new Chart(ctx2, {
    type: "doughnut",
    data: {
        labels: ["Fees Paid", "Fees Due"],
        datasets: [{
            data: [total_fees_paid, total_fees_due],
            backgroundColor: ["#dc3545", "#6c757d"]
        }]
    }
});
