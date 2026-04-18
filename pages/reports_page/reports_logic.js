// ==========================================
// GLOBAL STATE & CONFIG
// ==========================================
let chartInstances = {};
let currentChartType = "bar";
let isFiltered = false;

// ==========================================
// ENHANCED COLOR GRADIENT
// ==========================================
function getColor(value, max, min) {
  const range = max - min;
  const threshold1 = min + range * 0.33;
  const threshold2 = min + range * 0.66;

  if (value >= threshold2) return "rgba(76, 175, 80, 0.8)"; // Green
  if (value >= threshold1) return "rgba(255, 193, 7, 0.8)"; // Amber
  return "rgba(244, 67, 54, 0.8)"; // Red
}

// ==========================================
// UNIVERSAL CHART UPDATER
// ==========================================
function updateChart(canvasId, data, chartTitle, chartLabel) {
  const ctx = document.getElementById(canvasId);
  if (!ctx || !data?.data?.length) return;

  if (chartInstances[canvasId]) chartInstances[canvasId].destroy();

  const max = Math.max(...data.data);
  const min = Math.min(...data.data);

  chartInstances[canvasId] = new Chart(ctx, {
    type: currentChartType,
    data: {
      labels: data.labels,
      datasets: [
        {
          label: chartLabel,
          data: data.data,
          backgroundColor: data.data.map((v) => getColor(v, max, min)),
          borderColor: data.data.map((v) =>
            getColor(v, max, min).replace("0.8", "1"),
          ),
          borderWidth: currentChartType === "line" ? 3 : 2,
          tension: currentChartType === "line" ? 0.4 : 0,
          fill: currentChartType === "line",
          pointRadius: currentChartType === "line" ? 6 : 3,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true },
        title: {
          display: true,
          text: chartTitle,
          font: { size: 16, weight: "bold" },
        },
      },
      scales: currentChartType !== "pie" ? { y: { beginAtZero: true } } : {},
    },
  });
}

// ==========================================
// 🔥 ENHANCED FILTER (Uses your AJAX!)
// ==========================================
function applyEnhancedFilter() {
  const startDate = document.getElementById("start_date").value;
  const endDate = document.getElementById("end_date").value;

  const btn = document.getElementById("filterBtn");
  btn.innerHTML = "⏳ Loading...";
  btn.disabled = true;

  // 🔥 YOUR EXISTING AJAX
  fetch(
    `reports_data.php?ajax=filter_reports&start_date=${startDate}&end_date=${endDate}`,
  )
    .then((response) => response.json())
    .then((data) => {
      // Update global data
      window.chartData.readymix = data.readymix;
      window.chartData.mixture = data.mixture;
      window.chartData.sales = data.sales;

      // Refresh ALL charts
      Object.keys(chartInstances).forEach((id) => {
        const chart = chartInstances[id];
        const dataKey = id.replace("Chart", "").toLowerCase();
        chart.data.labels = window.chartData[dataKey].labels;
        chart.data.datasets[0].data = window.chartData[dataKey].data;
        chart.update("none");
      });

      // Visual success
      btn.innerHTML = `✅ ${data.sales.data.length || 0} designs`;
      btn.className = "btn btn-success btn-sm w-100 fw-bold p-2 h-100";

      isFiltered = true;
      console.log("🚀 Enhanced filter complete:", data.debug);
    })
    .catch((err) => {
      console.error("Filter error:", err);
      btn.innerHTML = "❌ Error";
    })
    .finally(() => {
      setTimeout(() => {
        btn.innerHTML = "Filter";
        btn.disabled = false;
        btn.className = "btn btn-primary btn-sm w-100 fw-bold p-2 h-100";
      }, 1500);
    });
}

// ==========================================
// CHART TYPE CYCLER
// ==========================================
function cycleChartType() {
  const types = ["bar", "line", "pie"];
  const names = ["Bar", "Line", "Pie"];
  const index = types.indexOf(currentChartType);
  currentChartType = types[(index + 1) % 3];

  Object.values(chartInstances).forEach((chart) => {
    chart.config.type = currentChartType;
    chart.update();
  });

  document.getElementById("chartToggle").textContent = names[(index + 1) % 3];
}

// ==========================================
// ENHANCED PDF EXPORT
// ==========================================
function enhancedPDF() {
  const opt = {
    margin: 0.5,
    filename: `Sorcom-Reports-${new Date().toISOString().split("T")[0]}.pdf`,
    image: { type: "jpeg", quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: "in", format: "a4", orientation: "landscape" },
  };

  // Hide debug/UI
  document.querySelector(".debug-div")?.style.display = "none";
  document
    .querySelectorAll(".table-toggle button")
    .forEach((btn) => btn.click());

  html2pdf()
    .set(opt)
    .from(".container-fluid")
    .save()
    .finally(() => {
      document.querySelector(".debug-div")?.style.display = "block";
    });
}

// ==========================================
// AUTO-REFRESH & INIT
// ==========================================
function initAdvancedControls() {
  // Chart toggle
  document.getElementById("chartToggle").onclick = cycleChartType;

  // Enhanced filter (overrides basic one)
  document.getElementById("filterBtn").onclick = applyEnhancedFilter;

  // PDF
  document.getElementById("pdfBtn").onclick = enhancedPDF;

  // Status
  console.log("🚀 Advanced controls loaded");
}

// Auto-refresh every 10min
setInterval(() => location.reload(), 600000);

document.addEventListener("DOMContentLoaded", initAdvancedControls);
