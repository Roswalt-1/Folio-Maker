document.addEventListener("DOMContentLoaded", () => {
    const page = document.querySelector("[data-portfolio-page]");
    if (!page) return;

    const menuWrap = page.querySelector("[data-menu-wrap]");
    const menuTrigger = page.querySelector("[data-menu-trigger]");
    const menu = page.querySelector('[role="menu"]');
    const toast = page.querySelector("[data-page-toast]");
    const deleteModal = page.querySelector("[data-delete-modal]");
    const cancelDelete = page.querySelector("[data-cancel-delete]");
    const deleteTrigger = page.querySelector("[data-open-delete]");
    const lightbox = page.querySelector("[data-lightbox]");
    const lightboxClose = page.querySelector("[data-lightbox-close]");
    const stickyToolbar = page.querySelector("[data-sticky-toolbar]");
    const mainToolbar = page.querySelector("[data-main-toolbar]");
    let toastTimer;
    let modalReturnFocus;
    let lightboxReturnFocus;

    const showToast = (message, isError = false) => {
        window.clearTimeout(toastTimer);
        toast.textContent = message;
        toast.classList.toggle("toast-error", isError);
        toast.hidden = false;
        toastTimer = window.setTimeout(() => {
            toast.hidden = true;
        }, 3000);
    };

    const menuItems = () => [...menu.querySelectorAll('[role="menuitem"]:not([disabled])')];
    const closeMenu = (restoreFocus = true) => {
        if (menu.hidden) return;
        menu.hidden = true;
        menuTrigger.setAttribute("aria-expanded", "false");
        if (restoreFocus) menuTrigger.focus();
    };

    menuTrigger.addEventListener("click", () => {
        const opening = menu.hidden;
        menu.hidden = !opening;
        menuTrigger.setAttribute("aria-expanded", String(opening));
        if (opening) menuItems()[0]?.focus();
    });

    menu.addEventListener("keydown", (event) => {
        const items = menuItems();
        const currentIndex = items.indexOf(document.activeElement);
        let nextIndex = currentIndex;
        if (event.key === "ArrowDown") nextIndex = (currentIndex + 1) % items.length;
        else if (event.key === "ArrowUp") nextIndex = (currentIndex - 1 + items.length) % items.length;
        else if (event.key === "Home") nextIndex = 0;
        else if (event.key === "End") nextIndex = items.length - 1;
        else if (event.key === "Escape") {
            event.preventDefault();
            closeMenu();
            return;
        } else {
            return;
        }
        event.preventDefault();
        items[nextIndex]?.focus();
    });

    document.addEventListener("pointerdown", (event) => {
        if (!menu.hidden && !menuWrap.contains(event.target)) closeMenu(false);
    });
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !menu.hidden) {
            event.preventDefault();
            closeMenu();
        }
    });

    const openDeleteModal = () => {
        closeMenu(false);
        modalReturnFocus = menuTrigger;
        deleteModal.hidden = false;
        cancelDelete.focus();
    };
    const closeDeleteModal = () => {
        if (deleteModal.hidden) return;
        deleteModal.hidden = true;
        modalReturnFocus?.focus();
    };

    deleteTrigger.addEventListener("click", openDeleteModal);
    cancelDelete.addEventListener("click", closeDeleteModal);
    deleteModal.addEventListener("click", (event) => {
        if (event.target === deleteModal) closeDeleteModal();
    });
    deleteModal.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            event.preventDefault();
            closeDeleteModal();
            return;
        }
        if (event.key !== "Tab") return;

        const focusable = [...deleteModal.querySelectorAll('button:not([disabled]), a[href], input:not([disabled])')];
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    page.querySelectorAll("[data-print-page]").forEach((button) => {
        button.addEventListener("click", () => {
            closeMenu(false);
            window.print();
        });
    });

    page.querySelectorAll("[data-unavailable]").forEach((button) => {
        button.addEventListener("click", () => {
            closeMenu();
            showToast(`${button.dataset.unavailable} is not available yet.`, true);
        });
    });

    page.querySelector("[data-share-page]").addEventListener("click", async () => {
        closeMenu();
        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(window.location.href);
            } else {
                const input = document.createElement("textarea");
                input.value = window.location.href;
                input.setAttribute("readonly", "");
                input.style.position = "fixed";
                input.style.opacity = "0";
                document.body.append(input);
                input.select();
                const copied = document.execCommand("copy");
                input.remove();
                if (!copied) throw new Error("Clipboard copy was rejected.");
            }
            showToast("Link copied");
        } catch (error) {
            console.error("Could not copy the portfolio link.", error);
            showToast("Could not copy the link. Check clipboard permissions and try again.", true);
        }
    });

    const openLightbox = page.querySelector("[data-cover-open]");
    if (openLightbox && lightbox && lightboxClose) {
        const closeLightbox = () => {
            lightbox.hidden = true;
            lightboxReturnFocus?.focus();
        };
        openLightbox.addEventListener("click", () => {
            lightboxReturnFocus = openLightbox;
            lightbox.hidden = false;
            lightboxClose.focus();
        });
        lightboxClose.addEventListener("click", closeLightbox);
        lightbox.addEventListener("click", (event) => {
            if (event.target === lightbox) closeLightbox();
        });
        lightbox.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                event.preventDefault();
                closeLightbox();
            }
        });
    }

    if (stickyToolbar && mainToolbar && "IntersectionObserver" in window) {
        const observer = new IntersectionObserver(([entry]) => {
            stickyToolbar.hidden = entry.isIntersecting || entry.boundingClientRect.top >= 0;
        }, { threshold: 0 });
        observer.observe(mainToolbar);
    }
});
