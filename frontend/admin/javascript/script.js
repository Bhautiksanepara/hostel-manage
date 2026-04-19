function toggleSidebar() {
  const sidebar = document.querySelector(".admin-sidebar");
  const topbar = document.querySelector(".admin-topbar");
  const content = document.querySelector(".admin-content");

  if (sidebar && topbar && content) {
    sidebar.classList.toggle("collapsed");
    topbar.classList.toggle("sidebar-collapsed");
    content.classList.toggle("sidebar-collapsed");
    localStorage.setItem(
      "adminSidebarCollapsed",
      sidebar.classList.contains("collapsed") ? "true" : "false",
    );
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.querySelector(".admin-sidebar");
  const topbar = document.querySelector(".admin-topbar");
  const content = document.querySelector(".admin-content");

  if (sidebar && topbar && content) {
    const isMobile = window.innerWidth <= 1024;
    const savedState = localStorage.getItem("adminSidebarCollapsed");
    const shouldCollapse = isMobile ? true : savedState === "true";

    sidebar.classList.toggle("collapsed", shouldCollapse);
    topbar.classList.toggle("sidebar-collapsed", shouldCollapse);
    content.classList.toggle("sidebar-collapsed", shouldCollapse);
  }

  initializeThemedSelects();
  initializeThemedDatePickers();
  initializeThemedTimePickers();
});

function closeThemedControls(exceptElement = null) {
  document
    .querySelectorAll(
      ".themed-select.open, .themed-date.open, .themed-time.open",
    )
    .forEach((wrapper) => {
      if (wrapper !== exceptElement) {
        wrapper.classList.remove("open");
      }
    });
}

document.addEventListener("click", (event) => {
  if (
    !event.target.closest(".themed-select") &&
    !event.target.closest(".themed-date") &&
    !event.target.closest(".themed-time")
  ) {
    closeThemedControls();
  }
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeThemedControls();
  }
});

function initializeThemedSelects() {
  const selects = document.querySelectorAll(
    "select:not(.themed-native-select)",
  );

  selects.forEach((select) => {
    if (select.dataset.themedInit === "true" || select.multiple) {
      return;
    }

    select.dataset.themedInit = "true";
    select.classList.add("themed-native-select");

    const wrapper = document.createElement("div");
    wrapper.className = "themed-select";
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "themed-select-trigger";

    const menu = document.createElement("div");
    menu.className = "themed-select-menu";

    wrapper.appendChild(trigger);
    wrapper.appendChild(menu);

    const rebuildOptions = () => {
      menu.innerHTML = "";

      Array.from(select.options).forEach((option, index) => {
        const item = document.createElement("button");
        item.type = "button";
        item.className = "themed-select-option";
        item.textContent = option.textContent;
        item.dataset.value = option.value;

        if (option.disabled) {
          item.disabled = true;
        }

        if (index === select.selectedIndex) {
          item.classList.add("is-selected");
        }

        item.addEventListener("click", () => {
          select.value = option.value;
          select.dispatchEvent(new Event("input", { bubbles: true }));
          select.dispatchEvent(new Event("change", { bubbles: true }));
          wrapper.classList.remove("open");
          trigger.focus();
        });

        menu.appendChild(item);
      });

      syncTrigger();
    };

    const syncTrigger = () => {
      const selectedOption = select.options[select.selectedIndex];
      const label = selectedOption
        ? selectedOption.textContent
        : "Select an option";
      const isPlaceholder = !select.value;

      trigger.textContent = label || "Select an option";
      trigger.classList.toggle("is-placeholder", isPlaceholder);

      Array.from(menu.children).forEach((item) => {
        item.classList.toggle(
          "is-selected",
          item.dataset.value === select.value,
        );
      });
    };

    trigger.addEventListener("click", () => {
      const shouldOpen = !wrapper.classList.contains("open");
      closeThemedControls(wrapper);
      wrapper.classList.toggle("open", shouldOpen);
    });

    select.addEventListener("change", syncTrigger);
    rebuildOptions();
  });
}

function initializeThemedDatePickers() {
  const dateInputs = document.querySelectorAll(
    'input[type="date"]:not(.themed-native-date)',
  );

  dateInputs.forEach((input) => {
    if (input.dataset.themedInit === "true") {
      return;
    }

    input.dataset.themedInit = "true";
    input.classList.add("themed-native-date");

    const wrapper = document.createElement("div");
    wrapper.className = "themed-date";
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "themed-date-trigger";

    const panel = document.createElement("div");
    panel.className = "themed-date-panel";

    wrapper.appendChild(trigger);
    wrapper.appendChild(panel);

    let viewDate = parseInputDate(input.value) || new Date();

    const syncTrigger = () => {
      const parsed = parseInputDate(input.value);
      trigger.textContent = parsed
        ? formatDisplayDate(parsed)
        : "Select a date";
      trigger.classList.toggle("is-placeholder", !parsed);
      if (parsed) {
        viewDate = parsed;
      }
    };

    const renderCalendar = () => {
      panel.innerHTML = "";

      const header = document.createElement("div");
      header.className = "themed-date-header";

      const prevButton = document.createElement("button");
      prevButton.type = "button";
      prevButton.className = "themed-date-nav";
      prevButton.textContent = "<";

      const title = document.createElement("div");
      title.className = "themed-date-title";
      title.textContent = viewDate.toLocaleDateString("en-IN", {
        month: "long",
        year: "numeric",
      });

      const nextButton = document.createElement("button");
      nextButton.type = "button";
      nextButton.className = "themed-date-nav";
      nextButton.textContent = ">";

      header.appendChild(prevButton);
      header.appendChild(title);
      header.appendChild(nextButton);
      panel.appendChild(header);

      const weekdays = document.createElement("div");
      weekdays.className = "themed-date-weekdays";

      ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"].forEach((day) => {
        const weekday = document.createElement("div");
        weekday.className = "themed-date-weekday";
        weekday.textContent = day;
        weekdays.appendChild(weekday);
      });

      panel.appendChild(weekdays);

      const grid = document.createElement("div");
      grid.className = "themed-date-grid";

      const firstDayOfMonth = new Date(
        viewDate.getFullYear(),
        viewDate.getMonth(),
        1,
      );
      const startDate = new Date(firstDayOfMonth);
      startDate.setDate(startDate.getDate() - startDate.getDay());
      const selectedValue = input.value;
      const today = formatInputDate(new Date());

      for (let index = 0; index < 42; index += 1) {
        const cellDate = new Date(startDate);
        cellDate.setDate(startDate.getDate() + index);

        const dayButton = document.createElement("button");
        dayButton.type = "button";
        dayButton.className = "themed-date-day";
        dayButton.textContent = cellDate.getDate();

        const formattedValue = formatInputDate(cellDate);

        if (cellDate.getMonth() !== viewDate.getMonth()) {
          dayButton.classList.add("is-outside");
        }

        if (formattedValue === selectedValue) {
          dayButton.classList.add("is-selected");
        }

        if (formattedValue === today) {
          dayButton.classList.add("is-today");
        }

        dayButton.addEventListener("click", () => {
          input.value = formattedValue;
          input.dispatchEvent(new Event("input", { bubbles: true }));
          input.dispatchEvent(new Event("change", { bubbles: true }));
          syncTrigger();
          renderCalendar();
          wrapper.classList.remove("open");
          trigger.focus();
        });

        grid.appendChild(dayButton);
      }

      panel.appendChild(grid);

      prevButton.addEventListener("click", () => {
        viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
        renderCalendar();
      });

      nextButton.addEventListener("click", () => {
        viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
        renderCalendar();
      });
    };

    trigger.addEventListener("click", () => {
      const shouldOpen = !wrapper.classList.contains("open");
      closeThemedControls(wrapper);
      wrapper.classList.toggle("open", shouldOpen);

      if (shouldOpen) {
        renderCalendar();
      }
    });

    input.addEventListener("change", () => {
      syncTrigger();
      renderCalendar();
    });

    syncTrigger();
    renderCalendar();
  });
}

function initializeThemedTimePickers() {
  const timeInputs = document.querySelectorAll(
    'input[type="time"]:not(.themed-native-time)',
  );

  timeInputs.forEach((input) => {
    if (input.dataset.themedInit === "true") {
      return;
    }

    input.dataset.themedInit = "true";
    input.classList.add("themed-native-time");

    const wrapper = document.createElement("div");
    wrapper.className = "themed-time";
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "themed-time-trigger";

    const panel = document.createElement("div");
    panel.className = "themed-time-panel";

    wrapper.appendChild(trigger);
    wrapper.appendChild(panel);

    const syncTrigger = () => {
      trigger.textContent = input.value
        ? formatDisplayTime(input.value)
        : "Select a time";
      trigger.classList.toggle("is-placeholder", !input.value);
    };

    const renderTimeOptions = () => {
      panel.innerHTML = "";
      const grid = document.createElement("div");
      grid.className = "themed-time-grid";

      for (let hour = 0; hour < 24; hour += 1) {
        for (let minute = 0; minute < 60; minute += 15) {
          const value = `${String(hour).padStart(2, "0")}:${String(minute).padStart(2, "0")}`;
          const option = document.createElement("button");
          option.type = "button";
          option.className = "themed-time-option";
          option.textContent = formatDisplayTime(value);

          if (value === input.value) {
            option.classList.add("is-selected");
          }

          option.addEventListener("click", () => {
            input.value = value;
            input.dispatchEvent(new Event("input", { bubbles: true }));
            input.dispatchEvent(new Event("change", { bubbles: true }));
            syncTrigger();
            renderTimeOptions();
            wrapper.classList.remove("open");
            trigger.focus();
          });

          grid.appendChild(option);
        }
      }

      panel.appendChild(grid);
    };

    trigger.addEventListener("click", () => {
      const shouldOpen = !wrapper.classList.contains("open");
      closeThemedControls(wrapper);
      wrapper.classList.toggle("open", shouldOpen);

      if (shouldOpen) {
        renderTimeOptions();
      }
    });

    input.addEventListener("change", () => {
      syncTrigger();
      renderTimeOptions();
    });

    syncTrigger();
    renderTimeOptions();
  });
}

function parseInputDate(value) {
  if (!value) {
    return null;
  }

  const [year, month, day] = value.split("-").map(Number);
  return new Date(year, month - 1, day);
}

function formatInputDate(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

function formatDisplayDate(date) {
  return date.toLocaleDateString("en-IN", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}

function formatDisplayTime(value) {
  const [hours, minutes] = value.split(":").map(Number);
  const period = hours >= 12 ? "PM" : "AM";
  const displayHour = ((hours + 11) % 12) + 1;
  return `${String(displayHour).padStart(2, "0")}:${String(minutes).padStart(2, "0")} ${period}`;
}
