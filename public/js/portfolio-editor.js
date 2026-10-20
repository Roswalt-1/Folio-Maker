(() => {
  "use strict";

  const fonts = ["Poppins", "Inter", "Playfair Display", "Roboto Mono", "Montserrat", "Lora", "Raleway", "Space Grotesk", "DM Sans"];
  const initial = JSON.parse(document.getElementById("editor-state").textContent);
  const storageKey = `folio-portfolio-draft:${initial.id ?? "new"}`;
  const defaults = {
    portfolio: { title: "", description: "", category: "", tags: "" },
    profile: {
      name: "", role: "", bio: "",
      contact: { email: "", phone: "", location: "", github: "", linkedin: "" },
      skills: [], education: [], experience: [],
      photo: { path: null, url: "", shape: "circle", zoom: 1, x: 50, y: 50 }
    },
    appearance: {
      style: { headingFont: "Space Grotesk", bodyFont: "DM Sans", fontSize: 16, accent: "#5b6e58", template: "minimal", radius: 14, spacing: 1 },
      background: { mode: "solid", solid: "#f8f7f2", color1: "#eef0e8", color2: "#dfe7dc", angle: 135, image_path: null, image_url: "", overlayOpacity: .22 },
      sections: { about: true, skills: true, projects: true, contact: true },
      theme: ""
    },
    items: [{ title: "", description: "", link: "", images: [] }],
    coverImage: ""
  };
  const clone = value => JSON.parse(JSON.stringify(value));
  const merge = (source, base) => {
    if (Array.isArray(base)) return Array.isArray(source) ? source : clone(base);
    if (base && typeof base === "object") {
      const result = {};
      for (const key of Object.keys(base)) result[key] = merge(source?.[key], base[key]);
      return result;
    }
    return source === undefined || source === null ? base : source;
  };
  const escapeHTML = value => String(value ?? "").replace(/[&<>"']/g, char => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
  })[char]);
  let state = merge(initial, defaults);
  const editor = document.getElementById("editor-form");
  const preview = document.getElementById("portfolio-preview");
  const status = document.getElementById("save-status");
  const notice = document.getElementById("editor-notice");
  const saveButton = document.getElementById("save-button");
  const uploads = { cover: null, photo: null, background: null };
  let statusTimeout;
  let currentPreviewUrls = [];

  function get(path) {
    return path.split(".").reduce((value, key) => value?.[key], state);
  }
  function collection(name) {
    return name === "items" ? state.items : state.profile[name];
  }
  function set(path, value) {
    const keys = path.split(".");
    const target = keys.slice(0, -1).reduce((object, key) => object[key], state);
    target[keys.at(-1)] = value;
  }
  function setStatus(message, isError = false, duration = 0) {
    clearTimeout(statusTimeout);
    status.textContent = message;
    status.classList.toggle("status-error", isError);
    if (duration) {
      statusTimeout = setTimeout(() => {
        status.textContent = "";
        status.classList.remove("status-error");
      }, duration);
    }
  }
  function safeURL(value) {
    if (!String(value || "").trim()) return "";
    try {
      const url = new URL(value, window.location.origin);
      return ["http:", "https:"].includes(url.protocol) ? url.href : "";
    } catch {
      return "";
    }
  }
  function initials(name) {
    return String(name || "?").trim().split(/\s+/).slice(0, 2).map(word => word[0] || "").join("").toUpperCase();
  }
  function displayRange(path) {
    const value = get(path);
    const label = path.endsWith("overlayOpacity") ? `${Math.round(Number(value) * 100)}%`
      : path.endsWith("spacing") ? `${Number(value).toFixed(2)}×`
      : path.endsWith("radius") ? `${value}px`
      : path.endsWith("angle") ? `${value}°`
      : `${value}px`;
    const output = document.querySelector(`[data-output="${path}"]`);
    if (output) output.value = output.textContent = label;
  }
  function syncControls() {
    document.querySelectorAll("[data-path]").forEach(control => {
      const value = get(control.dataset.path);
      if (control.type === "checkbox") control.checked = Boolean(value);
      else if (document.activeElement !== control) control.value = value ?? "";
    });
    document.querySelectorAll("[data-output]").forEach(control => displayRange(control.dataset.output));
    document.querySelectorAll("[data-background-fields]").forEach(section => {
      section.classList.toggle("hidden", state.appearance.background.mode !== section.dataset.backgroundFields);
    });
    document.querySelectorAll("[data-portfolio-field]").forEach(control => {
      const value = state.portfolio[control.dataset.portfolioField];
      if (document.activeElement !== control) control.value = value ?? "";
    });
    renderSkills();
    renderRepeaters();
    renderCurrentImages();
  }
  function renderSkills() {
    const container = document.getElementById("skills-editor");
    container.innerHTML = state.profile.skills.map((skill, index) => (
      `<span class="skill-chip">${escapeHTML(skill)}<button class="remove-chip" type="button" data-remove-skill="${index}" aria-label="Remove ${escapeHTML(skill)}">×</button></span>`
    )).join("");
  }
  function renderImageControls(collection, item, index) {
    if (collection !== "items") return "";
    const images = item.images || [];
    return `<div class="field"><span class="field-label">Project images</span><input type="file" accept="image/*" multiple data-item-upload="${index}">
      <div class="item-images">${images.map((image, imageIndex) => `<span class="existing-image"><img src="${escapeHTML(image.url)}" alt="Project image"><button type="button" data-remove-image="${index}" data-image-index="${imageIndex}" aria-label="Remove project image">×</button></span>`).join("")}</div>
      <p class="field-hint">Up to 8 images, 5 MB each.</p></div>`;
  }
  function repeatCard(collection, item, index) {
    const collectionList = collection === "items" ? state.items : state.profile[collection];
    const project = collection === "items";
    const label = project ? "Project" : collection === "education" ? "Education" : "Experience";
    return `<article class="repeat-card">
      <div class="repeat-head"><span>${label} ${index + 1}</span><div class="reorder-controls"><button type="button" data-move="${collection}" data-index="${index}" data-direction="-1" aria-label="Move ${label.toLowerCase()} up" ${index === 0 ? "disabled" : ""}>↑</button><button type="button" data-move="${collection}" data-index="${index}" data-direction="1" aria-label="Move ${label.toLowerCase()} down" ${index === collectionList.length - 1 ? "disabled" : ""}>↓</button><button class="remove-item" type="button" data-remove="${collection}" data-index="${index}" aria-label="Remove ${label.toLowerCase()} ${index + 1}">Remove ×</button></div></div>
      <label class="field"><span>${project ? "Project title" : "Degree or role"}</span><input type="text" data-collection="${collection}" data-index="${index}" data-key="title" value="${escapeHTML(item.title)}" placeholder="${project ? "Project name" : "Title"}"></label>
      ${project
        ? `<label class="field"><span>Description</span><textarea data-collection="${collection}" data-index="${index}" data-key="description" placeholder="What did you make?">${escapeHTML(item.description)}</textarea></label>
          <label class="field"><span>Project link</span><input type="url" data-collection="${collection}" data-index="${index}" data-key="link" value="${escapeHTML(item.link)}" placeholder="https://"></label>${renderImageControls(collection, item, index)}`
        : `<label class="field"><span>School or company</span><input type="text" data-collection="${collection}" data-index="${index}" data-key="organization" value="${escapeHTML(item.organization)}"></label>
          <label class="field"><span>Dates</span><input type="text" data-collection="${collection}" data-index="${index}" data-key="period" value="${escapeHTML(item.period)}" placeholder="2023 — 2025"></label>`}
    </article>`;
  }
  function renderRepeaters() {
    for (const collection of ["items", "education", "experience"]) {
      const element = document.getElementById(`${collection}-editor`);
      element.innerHTML = (collection === "items" ? state.items : state.profile[collection]).map((item, index) => repeatCard(collection, item, index)).join("");
    }
  }
  function renderCurrentImages() {
    const photo = document.getElementById("photo-current");
    photo.innerHTML = state.profile.photo.url
      ? `<img src="${escapeHTML(state.profile.photo.url)}" alt="Current profile photo"><button type="button" data-clear-image="photo">Remove saved photo</button>` : "";
    const background = document.getElementById("background-current");
    background.innerHTML = state.appearance.background.image_url
      ? `<img src="${escapeHTML(state.appearance.background.image_url)}" alt="Current background image"><button type="button" data-clear-image="background">Remove saved background</button>` : "";
    const cover = document.getElementById("cover-current");
    cover.innerHTML = state.coverImage ? `<img src="${escapeHTML(state.coverImage)}" alt="Current portfolio cover"><button type="button" data-clear-image="cover">Remove saved cover</button>` : "";
  }
  function renderPreview() {
    currentPreviewUrls.forEach(URL.revokeObjectURL);
    currentPreviewUrls = [];
    const photo = state.profile.photo;
    const photoURL = uploads.photo ? URL.createObjectURL(uploads.photo) : photo.url;
    if (uploads.photo) currentPreviewUrls.push(photoURL);
    const background = state.appearance.background;
    const bgURL = uploads.background ? URL.createObjectURL(uploads.background) : background.image_url;
    if (uploads.background) currentPreviewUrls.push(bgURL);
    const hero = `<header class="portfolio-hero"><div class="avatar shape-${escapeHTML(photo.shape)}">${photoURL
      ? `<img src="${escapeHTML(photoURL)}" alt="${escapeHTML(state.profile.name)} photo" style="object-position:${Number(photo.x)}% ${Number(photo.y)}%;transform:scale(${Number(photo.zoom)})">`
      : `<span class="avatar-placeholder" aria-hidden="true">${escapeHTML(initials(state.profile.name))}</span>`}</div>
      <div class="hero-copy"><p class="eyeline">${escapeHTML(state.portfolio.title || "PORTFOLIO")} / ${new Date().getFullYear()}</p><h1 class="portfolio-name">${escapeHTML(state.profile.name || "Your Name")}</h1><p class="portfolio-role">${escapeHTML(state.profile.role || "Your title or course")}</p><p class="portfolio-role">${escapeHTML([state.portfolio.category, state.portfolio.tags].filter(Boolean).join(" · "))}</p></div></header>`;
    const sections = state.appearance.sections;
    const about = sections.about ? `<section class="portfolio-section"><h2 class="section-kicker">About</h2><p class="portfolio-bio">${escapeHTML(state.profile.bio || state.portfolio.description || "Your introduction will appear here.")}</p></section>` : "";
    const skills = sections.skills ? `<section class="portfolio-section"><h2 class="section-kicker">Skills</h2><div class="portfolio-skills">${state.profile.skills.length ? state.profile.skills.map(skill => `<span class="portfolio-skill">${escapeHTML(skill)}</span>`).join("") : '<p class="empty-note">Add your skills in the editor.</p>'}</div></section>` : "";
    const projectCards = state.items.map(item => {
      const images = [...(item.images || [])];
      for (const file of item.newImages || []) {
        const url = URL.createObjectURL(file);
        currentPreviewUrls.push(url);
        images.push({ url });
      }
      return `<article class="project-card"><h3>${escapeHTML(item.title || "Untitled project")}</h3><p>${escapeHTML(item.description || "")}</p>${images.length ? `<div class="project-images">${images.map(image => `<img src="${escapeHTML(image.url)}" alt="${escapeHTML(item.title || "Project")} image">`).join("")}</div>` : ""}${safeURL(item.link) ? `<a href="${escapeHTML(safeURL(item.link))}">View project ↗</a>` : ""}</article>`;
    }).join("");
    const projects = sections.projects ? `<section class="portfolio-section"><h2 class="section-kicker">Selected projects</h2><div class="project-grid">${projectCards || '<p class="empty-note">Add your projects in the editor.</p>'}</div></section>` : "";
    const details = [...state.profile.education.map(item => ({ ...item, kind: "Education" })), ...state.profile.experience.map(item => ({ ...item, kind: "Experience" }))];
    const detailsHTML = details.length ? `<section class="portfolio-section"><div class="detail-grid">${details.map(item => `<article class="detail-card"><p class="eyeline">${item.kind}</p><h3>${escapeHTML(item.title)}</h3><p>${escapeHTML(item.organization)}</p><p class="detail-meta">${escapeHTML(item.period)}</p></article>`).join("")}</div></section>` : "";
    const contact = state.profile.contact;
    const contactLinks = [
      contact.email ? `<a href="mailto:${escapeHTML(contact.email)}">${escapeHTML(contact.email)}</a>` : "",
      contact.phone ? `<a href="tel:${escapeHTML(contact.phone)}">${escapeHTML(contact.phone)}</a>` : "",
      contact.location ? `<span>${escapeHTML(contact.location)}</span>` : "",
      ...[["GitHub", contact.github], ["LinkedIn", contact.linkedin]].map(([name, url]) => safeURL(url) ? `<a href="${escapeHTML(safeURL(url))}" target="_blank" rel="noopener noreferrer">${name} ↗</a>` : "")
    ].filter(Boolean);
    const contactHTML = sections.contact ? `<section class="portfolio-section"><h2 class="section-kicker">Contact</h2><div class="contact-list">${contactLinks.join("") || '<span class="empty-note">Add contact details.</span>'}</div></section>` : "";
    const imageStyle = background.mode === "image" && bgURL ? `--portfolio-image:url("${bgURL}");--portfolio-overlay:rgba(255,255,255,${background.overlayOpacity});` : "";
    const bg = background.mode === "gradient"
      ? `linear-gradient(${background.angle}deg,${background.color1},${background.color2})`
      : background.mode === "image" && bgURL ? "#f6f4ec" : background.solid;
    preview.className = `portfolio template-${state.appearance.style.template}${["dots", "grid", "waves", "stripes"].includes(background.mode) ? ` pattern-${background.mode}` : ""}`;
    preview.style.cssText = `--portfolio-bg:${bg};--portfolio-ink:${state.appearance.style.accent};--portfolio-muted:${state.appearance.style.accent};--portfolio-card:#fff;--portfolio-accent:${state.appearance.style.accent};--portfolio-radius:${state.appearance.style.radius}px;--portfolio-space:${state.appearance.style.spacing};--base-size:${state.appearance.style.fontSize}px;--portfolio-heading:"${state.appearance.style.headingFont}",sans-serif;--portfolio-body:"${state.appearance.style.bodyFont}",sans-serif;${imageStyle}`;
    if (background.mode === "dots") preview.style.backgroundImage = "radial-gradient(color-mix(in srgb, var(--portfolio-accent) 27%, transparent) 1px, transparent 1px)";
    if (background.mode === "grid") preview.style.backgroundImage = "linear-gradient(color-mix(in srgb, var(--portfolio-accent) 14%, transparent) 1px, transparent 1px),linear-gradient(90deg,color-mix(in srgb, var(--portfolio-accent) 14%, transparent) 1px,transparent 1px)";
    if (background.mode === "stripes") preview.style.backgroundImage = "repeating-linear-gradient(135deg,color-mix(in srgb,var(--portfolio-accent) 10%,transparent) 0 1px,transparent 1px 12px)";
    if (background.mode === "waves") preview.style.backgroundImage = "url(\"data:image/svg+xml,%3Csvg width='80' height='24' viewBox='0 0 80 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 12 Q10 2 20 12 T40 12 T60 12 T80 12' fill='none' stroke='%235b6e58' stroke-opacity='.22' stroke-width='1'/%3E%3C/svg%3E\")";
    preview.innerHTML = state.appearance.style.template === "sidebar"
      ? `<div class="portfolio-inner">${hero}<div class="sidebar-content">${about}${skills}${projects}${detailsHTML}${contactHTML}</div></div>`
      : `<div class="portfolio-inner">${hero}${about}${skills}${projects}${detailsHTML}${contactHTML}</div>`;
  }
  function render() {
    syncControls();
    renderPreview();
  }
  function saveDraft() {
    try {
      const draft = clone(state);
      draft.items.forEach(item => delete item.newImages);
      localStorage.setItem(storageKey, JSON.stringify({ savedAt: Date.now(), state: draft }));
    } catch (error) {
      console.warn("Could not store a local draft backup.", error);
    }
  }
  function changed() {
    notice.textContent = "";
    renderPreview();
    saveDraft();
  }
  function appendFile(formData, name, file) {
    if (file) formData.append(name, file);
  }
  async function savePortfolio(event) {
    event.preventDefault();
    if (!editor.reportValidity()) return;
    setStatus("Saving...");
    saveButton.disabled = true;
    notice.textContent = "";
    const formData = new FormData();
    const payload = {
      title: state.portfolio.title,
      description: state.portfolio.description,
      category: state.portfolio.category,
      tags: state.portfolio.tags,
      profile: state.profile,
      appearance: state.appearance,
      remove_cover: !state.coverImage && !uploads.cover,
      items: state.items.map(item => ({
        title: item.title, description: item.description, link: item.link,
        existing_images: (item.images || []).map(image => image.path)
      }))
    };
    payload.profile.photo.url = "";
    payload.appearance.background.image_url = "";
    formData.append("_method", editor.dataset.method);
    formData.append("remove_cover", String(payload.remove_cover));
    formData.append("editor_data", JSON.stringify(payload));
    appendFile(formData, "cover_image", uploads.cover);
    appendFile(formData, "profile_photo", uploads.photo);
    appendFile(formData, "background_image", uploads.background);
    state.items.forEach((item, index) => (item.newImages || []).forEach((file, fileIndex) => {
      formData.append(`items[${index}][images][${fileIndex}]`, file);
    }));
    try {
      const response = await fetch(editor.dataset.saveUrl, {
        method: "POST",
        headers: {
          "Accept": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          "X-HTTP-Method-Override": editor.dataset.method
        },
        body: formData
      });
      const result = await response.json();
      if (!response.ok) {
        const messages = Object.values(result.errors || {}).flat();
        throw new Error(messages.join(" ") || result.message || "Could not save your portfolio.");
      }
      localStorage.removeItem(storageKey);
      setStatus("Saved", false, 2200);
      setTimeout(() => window.location.assign(result.redirect), 2200);
    } catch (error) {
      setStatus(error.message || "Could not save your portfolio.", true, 2500);
    } finally {
      saveButton.disabled = false;
    }
  }
  function loadDraft() {
    try {
      const saved = JSON.parse(localStorage.getItem(storageKey) || "null");
      if (saved?.state && saved.savedAt > Date.parse(initial.updatedAt || 0)) {
        if (window.confirm("A newer local draft was found. Restore it over the saved portfolio?")) {
          state = merge(saved.state, defaults);
          render();
        }
      }
    } catch (error) {
      console.warn("Could not read the local draft backup.", error);
    }
  }
  function initializeFonts() {
    document.querySelectorAll('select[data-path$="Font"]').forEach(select => {
      select.innerHTML = fonts.map(font => `<option value="${escapeHTML(font)}">${escapeHTML(font)}</option>`).join("");
    });
  }
  function clearImage(kind) {
    if (kind === "photo") {
      state.profile.photo.path = null;
      state.profile.photo.url = "";
      uploads.photo = null;
      document.getElementById("photo-upload").value = "";
    } else if (kind === "background") {
      state.appearance.background.image_path = null;
      state.appearance.background.image_url = "";
      state.appearance.background.mode = "solid";
      uploads.background = null;
      document.getElementById("background-upload").value = "";
    } else {
      state.coverImage = "";
      uploads.cover = null;
      document.getElementById("cover-upload").value = "";
    }
    syncControls();
    changed();
  }

  initializeFonts();
  document.querySelectorAll(".accordion").forEach(section => {
    const summary = section.querySelector("summary");
    const content = section.querySelector(".accordion-content");
    const syncExpanded = () => {
      summary.setAttribute("aria-expanded", String(section.open));
      content.inert = !section.open;
    };
    syncExpanded();
    section.addEventListener("toggle", syncExpanded);
  });
  render();
  loadDraft();
  document.addEventListener("input", event => {
    const control = event.target.closest("[data-path]");
    if (control) {
      set(control.dataset.path, control.type === "checkbox" ? control.checked : control.type === "range" ? Number(control.value) : control.value);
      displayRange(control.dataset.path);
      renderPreview();
      changed();
      return;
    }
    const portfolioControl = event.target.closest("[data-portfolio-field]");
    if (portfolioControl) {
      state.portfolio[portfolioControl.dataset.portfolioField] = portfolioControl.value;
      renderPreview();
      changed();
      return;
    }
    const item = event.target.closest("[data-collection]");
    if (item) {
      collection(item.dataset.collection)[Number(item.dataset.index)][item.dataset.key] = item.value;
      renderPreview();
      changed();
    }
  });
  document.addEventListener("change", event => {
    const control = event.target.closest("[data-path]");
    if (control && control.tagName === "SELECT") {
      set(control.dataset.path, control.value);
      syncControls();
      renderPreview();
      changed();
    }
    if (event.target.matches("[data-item-upload]")) {
      const item = state.items[Number(event.target.dataset.itemUpload)];
      const files = [...event.target.files];
      if (((item.images || []).length + (item.newImages || []).length + files.length) > 8) {
        event.target.value = "";
        notice.textContent = "Each project can have up to 8 images.";
        return;
      }
      item.newImages = [...(item.newImages || []), ...files];
      changed();
    }
  });
  document.addEventListener("click", event => {
    const removeSkill = event.target.closest("[data-remove-skill]");
    if (removeSkill) {
      state.profile.skills.splice(Number(removeSkill.dataset.removeSkill), 1);
      render(); changed(); return;
    }
    const removeImage = event.target.closest("[data-remove-image]");
    if (removeImage) {
      state.items[Number(removeImage.dataset.removeImage)].images.splice(Number(removeImage.dataset.imageIndex), 1);
      render(); changed(); return;
    }
    const clear = event.target.closest("[data-clear-image]");
    if (clear) { clearImage(clear.dataset.clearImage); return; }
    const remove = event.target.closest("[data-remove]");
    if (remove) {
      collection(remove.dataset.remove).splice(Number(remove.dataset.index), 1);
      render(); changed(); return;
    }
    const move = event.target.closest("[data-move]");
    if (move) {
      const list = collection(move.dataset.move);
      const index = Number(move.dataset.index);
      const destination = index + Number(move.dataset.direction);
      if (destination >= 0 && destination < list.length) [list[index], list[destination]] = [list[destination], list[index]];
      render(); changed(); return;
    }
    const add = event.target.closest("[data-add]");
    if (add) {
      const blank = add.dataset.add === "items"
        ? { title: "", description: "", link: "", images: [], newImages: [] }
        : { title: "", organization: "", period: "" };
      collection(add.dataset.add).push(blank);
      render(); changed(); return;
    }
    const tab = event.target.closest("[data-mobile-tab]");
    if (tab) {
      document.getElementById("workspace").dataset.mobileView = tab.dataset.mobileTab;
      document.querySelectorAll("[data-mobile-tab]").forEach(button => button.setAttribute("aria-pressed", String(button === tab)));
    }
  });
  document.getElementById("skill-add").addEventListener("click", () => {
    const input = document.getElementById("skill-input");
    const value = input.value.trim();
    if (!value || state.profile.skills.includes(value)) return;
    state.profile.skills.push(value);
    input.value = "";
    render(); changed();
  });
  editor.addEventListener("submit", savePortfolio);
  document.getElementById("reset-button").addEventListener("click", () => {
    if (!window.confirm("Reset this editor to its default appearance and clear entered content?")) return;
    const blank = clone(defaults);
    blank.items = [];
    state = blank;
    Object.keys(uploads).forEach(key => { uploads[key] = null; });
    document.getElementById("photo-upload").value = "";
    document.getElementById("background-upload").value = "";
    document.getElementById("cover-upload").value = "";
    render(); changed();
  });
  document.getElementById("cover-upload").addEventListener("change", event => {
    uploads.cover = event.target.files[0] || null;
    renderCurrentImages(); changed();
  });
  document.getElementById("photo-upload").addEventListener("change", event => {
    uploads.photo = event.target.files[0] || null;
    render(); changed();
  });
  document.getElementById("background-upload").addEventListener("change", event => {
    uploads.background = event.target.files[0] || null;
    if (uploads.background) {
      state.appearance.background.mode = "image";
    }
    render(); changed();
  });
})();
