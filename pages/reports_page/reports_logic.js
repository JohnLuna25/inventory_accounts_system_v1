// 🔥 DEBUG VERSION - Replace your filterData/applyEnhancedFilter
window.debugFilter = function () {
  console.log("🔍 DEBUG START");

  const startDate = document.getElementById("start_date").value;
  const endDate = document.getElementById("end_date").value;

  fetch(
    `reports_data.php?ajax=filter_reports&start_date=${startDate}&end_date=${end_date}`,
  )
    .then((r) => {
      console.log("📡 Response OK:", r.status);
      return r.text(); // Get RAW text first
    })
    .then((text) => {
      console.log("📄 RAW RESPONSE:", text);
      const data = JSON.parse(text);
      console.log("✅ PARSED DATA:", data);
      console.log("readymix.data:", data.readymix?.data);

      // Test ONE chart
      if (data.readymix?.data?.length) {
        console.log("🟢 Creating readymix chart...");
        const canvas = document.getElementById("readymixChart");
        new Chart(canvas, {
          type: "bar",
          data: {
            labels: data.readymix.labels,
            datasets: [{ data: data.readymix.data, backgroundColor: "blue" }],
          },
        });
      }
    })
    .catch((e) => console.error("💥 FULL ERROR:", e));
};

// Add debug button temporarily

// ==========================================
// GLOBAL STATE & CONFIG
// ==========================================
let chartInstances = {};
let currentChartType = "bar";
let isFiltered = false;

// ==========================================
// ENHANCED COLOR GRADIENT (GOOD)
// ==========================================
function getColor(value, max, min) {
  const range = max - min;
  const threshold1 = min + range * 0.33;
  const threshold2 = min + range * 0.66;
  if (value >= threshold2) return "rgba(76, 175, 80, 0.8)";
  if (value >= threshold1) return "rgba(255, 193, 7, 0.8)";
  return "rgba(244, 67, 54, 0.8)";
}

// ==========================================
// 🔥 FIXED: updateAllCharts() - CALLS your updateChart()
// ==========================================
function updateAllCharts() {
  // ✅ ADD THIS FUNCTION
  const configs = [
    {
      id: "readymixChart",
      key: "readymix",
      title: "Readymix Inventory",
      label: "m³",
    },
    {
      id: "mixtureChart",
      key: "mixture",
      title: "Mixture Inventory",
      label: "Tons",
    },
    { id: "salesChart", key: "sales", title: "Sales Data", label: "m³" },
  ];

  configs.forEach((config) => {
    const data = window.chartData[config.key];
    if (data?.data?.length) {
      updateChart(config.id, data, config.title, config.label);
    }
  });
}

// ==========================================
// UNIVERSAL CHART UPDATER (GOOD - KEEP)
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
// 🔥 FIXED FILTER - Proper data flow
// ==========================================
function applyEnhancedFilter() {
  const startDate = document.getElementById("start_date").value;
  const endDate = document.getElementById("end_date").value;

  const btn = document.getElementById("filterBtn");
  btn.innerHTML = "⏳ Loading...";
  btn.disabled = true;

  fetch(
    `reports_data.php?ajax=filter_reports&start_date=${startDate}&end_date=${endDate}`,
  )
    .then((response) => response.json())
    .then((data) => {
      console.log("✅ DATA LOADED:", data);

      // 🔥 FIXED: Store in global state
      window.chartData.readymix = data.readymix;
      window.chartData.mixture = data.mixture;
      window.chartData.sales = data.sales;

      // 🔥 FIXED: Update charts
      updateAllCharts();

      btn.innerHTML = `✅ ${data.debug?.sales_count || 0}`;
      btn.className = "btn btn-success btn-sm w-100 fw-bold p-2 h-100";
    })
    .catch((err) => {
      console.error("❌ Filter error:", err);
      btn.innerHTML = "❌ Error";
    })
    .finally(() => {
      setTimeout(() => {
        btn.innerHTML = "🔄 Filter";
        btn.disabled = false;
        btn.className = "btn btn-primary btn-sm w-100 fw-bold p-2 h-100";
      }, 1500);
    });
}

// ==========================================
// CHART TYPE CYCLER (GOOD)
// ==========================================
function cycleChartType() {
  const types = ["bar", "line", "pie"];
  const names = ["📊 Bar", "📈 Line", "🥧 Pie"];
  const index = types.indexOf(currentChartType);
  currentChartType = types[(index + 1) % 3];

  // 🔥 FIX: Update button text FIRST
  const toggleBtn = document.getElementById("chartToggle");
  toggleBtn.innerHTML = names[(index + 1) % 3];

  // 🔥 FIX: DESTROY + REBUILD ALL CHARTS
  Object.keys(chartInstances).forEach((key) => {
    if (chartInstances[key]) {
      chartInstances[key].destroy();
      delete chartInstances[key];
    }
  });

  // 🔥 REBUILD with new type
  updateAllCharts();

  console.log(`🔄 Switched to: ${currentChartType}`);
}

// ==========================================
// INIT (FIXED)
// ==========================================
function initAdvancedControls() {
  document.getElementById("chartToggle").onclick = cycleChartType; // ✅ FIXED
  document.getElementById("filterBtn").onclick = applyEnhancedFilter;
  console.log("🚀 Controls ready");
}

document.addEventListener("DOMContentLoaded", initAdvancedControls);
