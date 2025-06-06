// Data simulasi
const completedCourseCount = document.getElementById("n-done").value;

// User Statistik
const patientCount = document.getElementById("patientCount").textContent;
const doctorCount = document.getElementById("doctorCount").textContent;

// Hitung persentase
const completedPercentage = (completedCourseCount / patientCount) * 100;
const incompletePercentage = 100 - completedPercentage;

// Registrasi plugin datalabels
Chart.register(ChartDataLabels);

// Buat grafik
const ctx = document.getElementById("chart").getContext("2d");
new Chart(ctx, {
    type: "bar",
    data: {
        labels: ["Pasien", "Tenaga Kesehatan"],
        datasets: [
            {
                label: "Jumlah",
                data: [patientCount, doctorCount],
                backgroundColor: [
                    "rgba(54, 162, 235, 0.6)",
                    "rgba(255, 99, 132, 0.6)",
                ],
                borderColor: ["rgba(54, 162, 235, 1)", "rgba(255, 99, 132, 1)"],
                borderWidth: 1,
            },
        ],
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: "rgb(22, 65, 80)",
                },
            },
            x: {
                ticks: {
                    color: "rgb(22, 65, 80)",
                },
            },
        },
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: "rgb(22, 65, 80)",
                },
            },
            title: {
                display: true,
                text: "Perbandingan Jumlah Pasien dan Tenaga Kesehatan",
                color: "rgb(22, 65, 80)",
            },
        },
    },
});

// Buat pie chart
const ctxPie = document.getElementById("pieChart").getContext("2d");
new Chart(ctxPie, {
    type: "pie",
    data: {
        labels: ["Kursus Selesai", "Belum selesai"],
        datasets: [
            {
                data: [completedPercentage, incompletePercentage],
                backgroundColor: [
                    "rgba(54, 162, 235, 0.6)",
                    "rgba(255, 99, 132, 0.6)",
                ],
                borderColor: ["rgba(75, 192, 192, 1)", "rgba(255, 99, 132, 1)"],
                borderWidth: 1,
            },
        ],
    },
    options: {
        responsive: true,
        plugins: {
            legend: { labels: { color: "rgb(22, 65, 80)" } },
            title: {
                display: true,
                text: "Persentase Penyelesaian Course",
                color: "white",
            },
            datalabels: {
                color: "white",
                formatter: (value) => value.toFixed(1) + "%",
                font: { weight: "bold", size: 14 },
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        const value = context.raw;
                        const count =
                            value === completedPercentage
                                ? completedCourseCount
                                : patientCount - completedCourseCount;
                        return `${context.label}: ${value.toFixed(
                            1
                        )}% (${count} pasien)`;
                    },
                },
            },
        },
    },
});
