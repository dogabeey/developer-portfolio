const projectList = document.querySelector("#project-list");
const currentYear = document.querySelector("#current-year");

const escapeHtml = (value) => {
  const element = document.createElement("div");
  element.textContent = value || "";
  return element.innerHTML;
};

fetch("projects.php")
  .then((response) => {
    if (!response.ok) throw new Error("Could not load projects");
    return response.json();
  })
  .then((projects) => {
    projectList.setAttribute("aria-busy", "false");
    if (!projects.length) {
      projectList.innerHTML = "<p>No projects have been published yet.</p>";
      return;
    }

    projectList.innerHTML = projects
      .map(({ id, title, role, description, project_url, thumbnail_url, screenshots }) => {
        const isLinked = /^https?:\/\//i.test(project_url || "");
        const linkAttributes = isLinked ? ` data-project-url="${escapeHtml(project_url)}" tabindex="0" role="link" aria-label="Open ${escapeHtml(title)} project"` : "";
        const screenshotMarkup = (screenshots || []).map((url, index) => `<img src="${escapeHtml(url)}" alt="${escapeHtml(title)} screenshot ${index + 1}" loading="lazy">`).join("");
        return `
          <article class="project-card${isLinked ? " project-card-link" : ""}"${linkAttributes}>
            ${thumbnail_url ? `<img class="project-thumbnail" src="${escapeHtml(thumbnail_url)}" alt="${escapeHtml(title)} thumbnail" loading="lazy">` : ""}
            <div class="project-card-content">
              <div class="project-role-row"><span>${escapeHtml(role)}</span>${window.isAdminLoggedIn && id ? `<a class="project-edit" href="admin/edit-project.php?id=${id}">Edit</a>` : ""}</div>
              <h3>${escapeHtml(title)}</h3>
              <p class="project-description">${escapeHtml(description)}</p>
              ${screenshotMarkup ? `<div class="screenshot-strip">${screenshotMarkup}</div>` : ""}
            </div>
          </article>
        `;
      })
      .join("");

    document.querySelectorAll(".project-card-link").forEach((card) => {
      const openProject = () => window.open(card.dataset.projectUrl, "_blank", "noopener");
      card.addEventListener("click", (event) => {
        if (!event.target.closest("a, button, input, textarea, select")) openProject();
      });
      card.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          openProject();
        }
      });
    });
  })
  .catch(() => {
    projectList.setAttribute("aria-busy", "false");
    projectList.innerHTML = "<p>Projects are unavailable right now. Please try again later.</p>";
  });

currentYear.textContent = new Date().getFullYear();
