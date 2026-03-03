// Color Coding Function
function getColor(value, max, min) {
  const range = max - min;
  const threshold1 = min + range * 0.33;
  const threshold2 = min + range * 0.66;

  if (value >= threshold2) return "rgba(76, 175, 80, 0.7)"; // Green
  if (value >= threshold1) return "rgba(255, 193, 7, 0.7)"; // Yellow
  return "rgba(244, 67, 54, 0.7)"; // Red
}

// Chart 1: Readymix Inventory
function initReadymixChart(data) {
  const ctx = document.getElementById("readymixChart");
  if (!ctx) {
    console.error("Canvas #readymixChart not found!");
    return;
  }

  const max = Math.max(...data.data);
  const min = Math.min(...data.data);

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.labels,
      datasets: [
        {
          label: "Total Stock (m³)",
          data: data.data,
          backgroundColor: data.data.map((value, index) =>
            getColor(value, max, min),
          ),
          borderColor: data.data.map((value, index) =>
            getColor(value, max, min).replace("0.7", "1"),
          ),
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
        },
        title: {
          display: true,
          text: "Readymix Inventory by Design Mix",
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

// Chart 2: Mixture Inventory
function initMixtureChart(data) {
  const ctx = document.getElementById("mixtureChart");
  if (!ctx) {
    console.error("Canvas #mixtureChart not found!");
    return;
  }

  const max = Math.max(...data.data);
  const min = Math.min(...data.data);

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.labels,
      datasets: [
        {
          label: "Stock (kgs)",
          data: data.data,
          backgroundColor: data.data.map((value, index) =>
            getColor(value, max, min),
          ),
          borderColor: data.data.map((value, index) =>
            getColor(value, max, min).replace("0.7", "1"),
          ),
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
        },
        title: {
          display: true,
          text: "Current Mixture Stock Levels",
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

// Chart 3: Most Sold Design Mix
function initSalesChart(data) {
  const ctx = document.getElementById("salesChart");
  if (!ctx) {
    console.error("Canvas #salesChart not found!");
    return;
  }

  const max = Math.max(...data.data);
  const min = Math.min(...data.data);

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.labels,
      datasets: [
        {
          label: "Total Sold (m³)",
          data: data.data,
          backgroundColor: data.data.map((value, index) =>
            getColor(value, max, min),
          ),
          borderColor: data.data.map((value, index) =>
            getColor(value, max, min).replace("0.7", "1"),
          ),
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
        },
        title: {
          display: true,
          text: "Top 5 Most Sold Design Mixes",
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

// Export to PDF
function exportPDF() {
  const element = document.querySelector(".container-fluid");
  const opt = {
    margin: 1,
    filename: "sorcom-reports.pdf",
    image: { type: "jpeg", quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: "in", format: "letter", orientation: "landscape" },
  };
  html2pdf().set(opt).from(element).save();
}

// Auto-refresh every 30 seconds
setInterval(function () {
  location.reload();
}, 30000);

// Initialize Charts on Page Load
document.addEventListener("DOMContentLoaded", function () {
  console.log("=== DEBUG: Chart Initialization ===");
  console.log("Initializing Readymix Chart...");
  initReadymixChart(readymixData);
  console.log("Initializing Mixture Chart...");
  initMixtureChart(mixtureData);
  console.log("Initializing Sales Chart...");
  initSalesChart(salesData);
  console.log("=== END DEBUG ===");
});
