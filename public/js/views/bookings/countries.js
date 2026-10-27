
/* ============================================
   PITO HOTEL - COUNTRY DROPDOWN V6
   Search + Click + Select
   Browser Popover API
   Thailand excluded
============================================ */

document.addEventListener("DOMContentLoaded", () => {
    "use strict";

    const dialog = document.getElementById("guestDialog");
    const picker = document.getElementById("guestCountryPicker");
    const input = document.getElementById("guestNationality");
    const toggle = document.getElementById("guestCountryToggle");
    const menu = document.getElementById("guestCountryMenu");
    const form = document.getElementById("guestForm");

    if (!dialog || !picker || !input || !toggle || !menu) {
        return;
    }

    if (
        typeof menu.showPopover !== "function" ||
        typeof Intl.DisplayNames !== "function"
    ) {
        console.error(
            "Please use a recent version of Chrome or Edge."
        );
        return;
    }

    /* ============================================
       COUNTRY LIST
    ============================================ */

    const codes = `
        AD AE AF AG AI AL AM AO AQ AR AS AT AU AW AX AZ
        BA BB BD BE BF BG BH BI BJ BL BM BN BO BQ BR
        BS BT BV BW BY BZ CA CC CD CF CG CH CI CK CL
        CM CN CO CR CU CV CW CX CY CZ DE DJ DK DM DO
        DZ EC EE EG EH ER ES ET FI FJ FK FM FO FR GA
        GB GD GE GF GG GH GI GL GM GN GP GQ GR GS GT
        GU GW GY HK HM HN HR HT HU ID IE IL IM IN IO
        IQ IR IS IT JE JM JO JP KE KG KH KI KM KN KP
        KR KW KY KZ LA LB LC LI LK LR LS LT LU LV LY
        MA MC MD ME MF MG MH MK ML MM MN MO MP MQ MR
        MS MT MU MV MW MX MY MZ NA NC NE NF NG NI NL
        NO NP NR NU NZ OM PA PE PF PG PH PK PL PM PN
        PR PS PT PW PY QA RE RO RS RU RW SA SB SC SD
        SE SG SH SI SJ SK SL SM SN SO SR SS ST SV SX
        SY SZ TC TD TF TG TH TJ TK TL TM TN TO TR TT
        TV TW TZ UA UG UM US UY UZ VA VC VE VG VI VN
        VU WF WS YE YT ZA ZM ZW
    `.trim().split(/\s+/);

    const regionNames = new Intl.DisplayNames(
        ["en"],
        { type: "region" }
    );

    /* ============================================
       REMOVE THAILAND
    ============================================ */

    const allCountries = Array.from(
        new Set(
            codes
                .filter(code => code !== "TH")
                .map(code => regionNames.of(code))
                .filter(Boolean)
                .concat("Kosovo")
        )
    ).sort((a, b) => a.localeCompare(b, "en"));

    // Show Cambodia first
    const countries = [
        "Cambodia",
        ...allCountries.filter(
            country => country !== "Cambodia"
        )
    ];

    /* ============================================
       CHECK DROPDOWN STATUS
    ============================================ */

    function isOpen() {
        return menu.matches(":popover-open");
    }

    /* ============================================
       CLOSE DROPDOWN
    ============================================ */

    function closeMenu() {
        if (isOpen()) {
            menu.hidePopover();
        }

        input.setAttribute("aria-expanded", "false");
    }

    /* ============================================
       DROPDOWN POSITION
       Prevent popup scrolling problems
    ============================================ */

    function positionMenu() {
        if (!isOpen()) return;

        const rect = picker.getBoundingClientRect();

        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        const spaceBelow =
            viewportHeight - rect.bottom - 12;

        const spaceAbove = rect.top - 12;

        const openAbove =
            spaceBelow < 210 &&
            spaceAbove > spaceBelow;

        const availableSpace = openAbove
            ? spaceAbove
            : spaceBelow;

        const height = Math.min(
            250,
            Math.max(90, availableSpace - 10)
        );

        const width = Math.min(
            rect.width,
            viewportWidth - 16
        );

        const left = Math.max(
            8,
            Math.min(
                rect.left,
                viewportWidth - width - 8
            )
        );

        const top = openAbove
            ? Math.max(
                8,
                rect.top - height - 6
            )
            : Math.min(
                viewportHeight - height - 8,
                rect.bottom + 6
            );

        menu.style.width = `${width}px`;
        menu.style.maxHeight = `${height}px`;
        menu.style.left = `${left}px`;
        menu.style.top = `${Math.max(8, top)}px`;
    }

    /* ============================================
       SELECT COUNTRY
    ============================================ */

    function chooseCountry(country) {
        input.value = country;

        input.setCustomValidity("");

        input.dispatchEvent(
            new Event("change", {
                bubbles: true
            })
        );

        closeMenu();

        input.focus({
            preventScroll: true
        });
    }

    /* ============================================
       DISPLAY COUNTRY OPTIONS
    ============================================ */

    function renderCountries(showAll = false) {
        const query = showAll
            ? ""
            : input.value.trim().toLowerCase();

        const matching = query
            ? countries.filter(country =>
                country.toLowerCase().includes(query)
            )
            : countries;

        menu.replaceChildren();

        // No results
        if (matching.length === 0) {
            const empty = document.createElement("div");

            empty.className = "rv-country-empty";
            empty.textContent = "No countries found";

            menu.appendChild(empty);
            return;
        }

        matching.forEach(country => {
            const button =
                document.createElement("button");

            button.type = "button";
            button.className = "rv-country-option";
            button.textContent = country;

            button.setAttribute("role", "option");

            button.setAttribute(
                "aria-selected",
                String(input.value === country)
            );

            if (input.value === country) {
                button.classList.add("selected");
            }

            button.addEventListener("click", () => {
                chooseCountry(country);
            });

            menu.appendChild(button);
        });
    }

    /* ============================================
       OPEN DROPDOWN
    ============================================ */

    function openMenu(showAll = true) {
        if (!dialog.open) return;

        renderCountries(showAll);

        if (!isOpen()) {
            menu.showPopover();
        }

        input.setAttribute(
            "aria-expanded",
            "true"
        );

        positionMenu();
    }

    /* ============================================
       CLICK COUNTRY INPUT
    ============================================ */

    input.addEventListener("click", () => {
        openMenu(true);
    });

    /* ============================================
       CLICK DROPDOWN ARROW
    ============================================ */

    toggle.addEventListener("click", () => {
        if (isOpen()) {
            closeMenu();
        } else {
            openMenu(true);
        }
    });

    /* ============================================
       SEARCH COUNTRY
    ============================================ */

    input.addEventListener("input", () => {
        input.setCustomValidity("");
        openMenu(false);
    });

    /* ============================================
       KEYBOARD NAVIGATION
    ============================================ */

    input.addEventListener("keydown", event => {

        if (event.key === "Escape") {

            if (isOpen()) {
                event.preventDefault();
                closeMenu();
            }

        } else if (event.key === "ArrowDown") {

            event.preventDefault();

            if (!isOpen()) {
                openMenu(true);
            }

            menu.querySelector(
                ".rv-country-option"
            )?.focus();

        } else if (
            event.key === "Enter" &&
            isOpen()
        ) {

            const firstOption = menu.querySelector(
                ".rv-country-option"
            );

            if (firstOption) {
                event.preventDefault();
                firstOption.click();
            }
        }
    });

    menu.addEventListener("keydown", event => {

        const options = [
            ...menu.querySelectorAll(
                ".rv-country-option"
            )
        ];

        const index = options.indexOf(
            document.activeElement
        );

        if (event.key === "Escape") {

            event.preventDefault();
            closeMenu();
            input.focus();

        } else if (event.key === "ArrowDown") {

            event.preventDefault();

            options[
                Math.min(
                    index + 1,
                    options.length - 1
                )
            ]?.focus();

        } else if (event.key === "ArrowUp") {

            event.preventDefault();

            if (index <= 0) {
                input.focus();
            } else {
                options[index - 1]?.focus();
            }
        }
    });

    /* ============================================
       CLOSE WHEN CLICKING OUTSIDE
    ============================================ */

    document.addEventListener(
        "pointerdown",
        event => {

            if (
                !picker.contains(event.target) &&
                !menu.contains(event.target)
            ) {
                closeMenu();
            }
        }
    );

    /* ============================================
       CLOSE WHEN DIALOG CLOSES
    ============================================ */

    dialog.addEventListener(
        "close",
        closeMenu
    );

    /* ============================================
       REPOSITION WHEN SCREEN CHANGES
    ============================================ */

    window.addEventListener(
        "resize",
        positionMenu
    );

    dialog.querySelector(".rv-dialog-body")
        ?.addEventListener(
            "scroll",
            positionMenu
        );

    /* ============================================
       VALIDATION
    ============================================ */

    if (form) {
        form.addEventListener(
            "submit",
            event => {

                const value = input.value
                    .trim()
                    .toLowerCase();

                if (value === "thailand") {

                    event.preventDefault();

                    input.setCustomValidity(
                        "Please choose another country."
                    );

                    input.reportValidity();
                }
            }
        );
    }

});
