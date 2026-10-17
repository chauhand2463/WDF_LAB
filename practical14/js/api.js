(function () {
    "use strict";

    var config = window.STUDENTHUB_API || null;

    if (!config) {
        return;
    }

    function el(id) {
        return document.getElementById(id);
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function debounce(fn, delay) {
        var timer = null;

        return function () {
            var args = arguments;
            var context = this;

            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(context, args);
            }, delay);
        };
    }

    function setStatus(message, type) {
        var box = el("ajaxStatus");

        if (!box) {
            return;
        }

        if (!message) {
            box.hidden = true;
            box.textContent = "";
            box.className = "ajax-status";
            return;
        }

        box.hidden = false;
        box.textContent = message;
        box.className = "ajax-status " + (type || "info");
    }

    function apiRequest(url, options) {
        options = options || {};

        var method = options.method || "GET";
        var headers = {
            Accept: "application/json"
        };

        var fetchOptions = {
            method: method,
            headers: headers,
            credentials: "same-origin"
        };

        if (options.body !== undefined && method !== "GET") {
            headers["Content-Type"] = "application/json";
            headers["X-CSRF-Token"] = config.csrf || "";
            fetchOptions.body = JSON.stringify(options.body);
        }

        var fullUrl = url;

        if (options.params) {
            var clean = {};

            Object.keys(options.params).forEach(function (key) {
                var value = options.params[key];

                if (value !== "" && value !== null && value !== undefined) {
                    clean[key] = value;
                }
            });

            var query = new URLSearchParams(clean).toString();

            if (query !== "") {
                fullUrl += "?" + query;
            }
        }

        return fetch(fullUrl, fetchOptions).then(function (response) {
            return response
                .json()
                .catch(function () {
                    return { success: false, error: "Invalid server response." };
                })
                .then(function (data) {
                    if (!response.ok || data.success === false) {
                        var error = new Error(
                            data.error || "Request failed with status " + response.status + "."
                        );

                        error.status = response.status;
                        error.payload = data;
                        throw error;
                    }

                    return data;
                });
        });
    }

    function wireStudentSection() {
        var form = el("studentForm");

        if (!form) {
            return;
        }

        var tableBody = el("studentTableBody");
        var statusCell = el("studentTableStatus");
        var searchInput = el("studentSearch");
        var courseInput = el("studentCourseFilter");
        var pagination = el("studentPagination");
        var heading = el("studentFormHeading");
        var submitBtn = el("studentSubmitBtn");
        var cancelBtn = el("studentCancelBtn");
        var idInput = el("student_id");

        var state = {
            page: 1,
            limit: 8,
            search: "",
            course: "",
            total: 0
        };

        function setEditing(row) {
            form.dataset.mode = row ? "edit" : "add";

            if (row) {
                idInput.value = row.student_id;
                idInput.readOnly = true;
                el("student_full_name").value = row.full_name;
                el("student_email").value = row.email;
                el("student_mobile").value = row.mobile;
                el("student_course").value = row.course;
                el("student_password").value = "";
                heading.textContent = "Edit Student (" + row.student_id + ")";
                submitBtn.textContent = "Update Student";
                cancelBtn.hidden = false;
            } else {
                form.reset();
                idInput.value = "";
                idInput.readOnly = false;
                heading.textContent = "Add New Student";
                submitBtn.textContent = "Add Student";
                cancelBtn.hidden = true;
            }
        }

        function renderRows(rows) {
            if (!rows.length) {
                tableBody.innerHTML =
                    '<tr><td colspan="6" class="empty-row">No students found.</td></tr>';
                return;
            }

            tableBody.innerHTML = rows
                .map(function (row) {
                    return (
                        "<tr>" +
                        "<td>" + escapeHtml(row.student_id) + "</td>" +
                        "<td>" + escapeHtml(row.full_name) + "</td>" +
                        "<td>" + escapeHtml(row.email) + "</td>" +
                        "<td>" + escapeHtml(row.mobile) + "</td>" +
                        "<td>" + escapeHtml(row.course) + "</td>" +
                        '<td class="row-actions">' +
                        '<button type="button" class="btn edit-btn" data-action="edit" ' +
                        'data-id="' + escapeHtml(row.student_id) + '">Edit</button> ' +
                        '<button type="button" class="btn delete-btn" data-action="delete" ' +
                        'data-id="' + escapeHtml(row.student_id) + '">Delete</button>' +
                        "</td>" +
                        "</tr>"
                    );
                })
                .join("");
        }

        function renderPagination() {
            var pages = state.limit > 0 ? Math.ceil(state.total / state.limit) : 0;

            if (pages <= 1) {
                pagination.innerHTML = "";
                return;
            }

            var html = '<button type="button" class="btn page-btn" data-page="' +
                Math.max(1, state.page - 1) + '"' +
                (state.page <= 1 ? " disabled" : "") + ">Prev</button>";

            html += '<span class="page-info">Page ' + state.page + " of " + pages + "</span>";

            html += '<button type="button" class="btn page-btn" data-page="' +
                Math.min(pages, state.page + 1) + '"' +
                (state.page >= pages ? " disabled" : "") + ">Next</button>";

            pagination.innerHTML = html;
        }

        function loadCourseOptions() {
            if (!courseInput || courseInput.dataset.loaded === "yes") {
                return;
            }

            apiRequest(config.studentsUrl, {
                params: { limit: 100, page: 1 }
            })
                .then(function (data) {
                    var seen = {};

                    (data.data || []).forEach(function (row) {
                        var course = (row.course || "").trim();

                        if (course !== "") {
                            seen[course] = true;
                        }
                    });

                    Object.keys(seen)
                        .sort()
                        .forEach(function (course) {
                            var option = document.createElement("option");

                            option.value = course;
                            option.textContent = course;
                            courseInput.appendChild(option);
                        });

                    courseInput.dataset.loaded = "yes";
                })
                .catch(function () {
                });
        }

        function loadStudents() {
            if (statusCell) {
                statusCell.hidden = false;
                statusCell.textContent = "Loading...";
            }

            apiRequest(config.studentsUrl, {
                params: {
                    search: state.search,
                    course: state.course,
                    page: state.page,
                    limit: state.limit
                }
            })
                .then(function (data) {
                    state.total = data.total || 0;
                    renderRows(data.data || []);
                    renderPagination();

                    if (statusCell) {
                        statusCell.hidden = true;
                    }
                })
                .catch(function (error) {
                    if (statusCell) {
                        statusCell.hidden = false;
                        statusCell.textContent = error.message;
                    }
                });
        }

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            var payload = {
                full_name: el("student_full_name").value.trim(),
                email: el("student_email").value.trim(),
                mobile: el("student_mobile").value.trim(),
                course: el("student_course").value.trim(),
                password: el("student_password").value
            };

            var isEdit = form.dataset.mode === "edit";

            if (isEdit) {
                payload.student_id = idInput.value;
            } else {
                payload.student_id = idInput.value.trim();
            }

            submitBtn.disabled = true;

            apiRequest(config.studentsUrl, {
                method: isEdit ? "PUT" : "POST",
                body: payload
            })
                .then(function (data) {
                    setStatus(data.message || "Saved successfully.", "success");
                    setEditing(null);
                    loadStudents();
                })
                .catch(function (error) {
                    var detail = error.payload && error.payload.errors
                        ? " " + Object.keys(error.payload.errors)
                            .map(function (key) { return error.payload.errors[key]; })
                            .join(" ")
                        : "";

                    setStatus(error.message + detail, "error");
                })
                .finally(function () {
                    submitBtn.disabled = false;
                });
        });

        cancelBtn.addEventListener("click", function () {
            setEditing(null);
        });

        tableBody.addEventListener("click", function (event) {
            var button = event.target.closest("button[data-action]");

            if (!button) {
                return;
            }

            var studentId = button.getAttribute("data-id");

            if (button.getAttribute("data-action") === "edit") {
                apiRequest(config.studentsUrl, {
                    params: { id: studentId }
                })
                    .then(function (data) {
                        setEditing(data.data);
                        window.scrollTo({ top: 0, behavior: "smooth" });
                    })
                    .catch(function (error) {
                        setStatus(error.message, "error");
                    });
                return;
            }

            if (
                button.getAttribute("data-action") === "delete" &&
                window.confirm("Delete student " + studentId + "? This cannot be undone.")
            ) {
                apiRequest(config.studentsUrl, {
                    method: "DELETE",
                    params: { id: studentId }
                })
                    .then(function (data) {
                        setStatus(data.message || "Deleted.", "success");
                        loadStudents();
                    })
                    .catch(function (error) {
                        setStatus(error.message, "error");
                    });
            }
        });

        pagination.addEventListener("click", function (event) {
            var button = event.target.closest("button[data-page]");

            if (!button) {
                return;
            }

            state.page = parseInt(button.getAttribute("data-page"), 10) || 1;
            loadStudents();
        });

        if (searchInput) {
            searchInput.addEventListener(
                "input",
                debounce(function () {
                    state.search = searchInput.value.trim();
                    state.page = 1;
                    loadStudents();
                }, 350)
            );
        }

        if (courseInput) {
            courseInput.addEventListener("change", function () {
                state.course = courseInput.value;
                state.page = 1;
                loadStudents();
            });
        }

        window.refreshStudentApiList = loadStudents;

        loadCourseOptions();
        loadStudents();
    }

    function wireEventSection() {
        var form = el("eventForm");

        if (!form) {
            return;
        }

        var tableBody = el("eventTableBody");
        var statusCell = el("eventTableStatus");
        var searchInput = el("eventSearch");
        var statusInput = el("eventStatusFilter");
        var heading = el("eventFormHeading");
        var submitBtn = el("eventSubmitBtn");
        var cancelBtn = el("eventCancelBtn");
        var idInput = el("event_id");

        var state = {
            search: "",
            status: ""
        };

        function setEditing(row) {
            form.dataset.mode = row ? "edit" : "add";

            if (row) {
                idInput.value = row.event_id;
                el("event_name").value = row.event_name;
                el("event_date").value = row.event_date;
                el("event_category").value = row.category;
                el("event_status").value = row.status;
                el("event_description").value = row.description;
                heading.textContent = "Edit Event #" + row.event_id;
                submitBtn.textContent = "Update Event";
                cancelBtn.hidden = false;
            } else {
                form.reset();
                idInput.value = "";
                heading.textContent = "Add New Event";
                submitBtn.textContent = "Add Event";
                cancelBtn.hidden = true;
            }
        }

        function renderRows(rows) {
            if (!rows.length) {
                tableBody.innerHTML =
                    '<tr><td colspan="6" class="empty-row">No events found.</td></tr>';
                return;
            }

            tableBody.innerHTML = rows
                .map(function (row) {
                    var badgeClass = row.status === "Closed"
                        ? "status-closed"
                        : "status-open";

                    return (
                        "<tr>" +
                        "<td>" + escapeHtml(row.event_id) + "</td>" +
                        "<td>" + escapeHtml(row.event_name) + "</td>" +
                        "<td>" + escapeHtml(row.event_date) + "</td>" +
                        "<td>" + escapeHtml(row.category || "—") + "</td>" +
                        '<td><span class="status-badge ' + badgeClass + '">' +
                        escapeHtml(row.status) + "</span></td>" +
                        '<td class="row-actions">' +
                        '<button type="button" class="btn edit-btn" data-action="edit" ' +
                        'data-id="' + escapeHtml(row.event_id) + '">Edit</button> ' +
                        '<button type="button" class="btn delete-btn" data-action="delete" ' +
                        'data-id="' + escapeHtml(row.event_id) + '">Delete</button>' +
                        "</td>" +
                        "</tr>"
                    );
                })
                .join("");
        }

        function loadEvents() {
            if (statusCell) {
                statusCell.hidden = false;
                statusCell.textContent = "Loading...";
            }

            apiRequest(config.eventsUrl, {
                params: {
                    search: state.search,
                    status: state.status
                }
            })
                .then(function (data) {
                    renderRows(data.data || []);

                    if (statusCell) {
                        statusCell.hidden = true;
                    }
                })
                .catch(function (error) {
                    if (statusCell) {
                        statusCell.hidden = false;
                        statusCell.textContent = error.message;
                    }
                });
        }

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            var isEdit = form.dataset.mode === "edit";

            var payload = {
                event_name: el("event_name").value.trim(),
                event_date: el("event_date").value,
                category: el("event_category").value.trim(),
                status: el("event_status").value,
                description: el("event_description").value.trim()
            };

            if (isEdit) {
                payload.event_id = parseInt(idInput.value, 10);
            }

            submitBtn.disabled = true;

            apiRequest(config.eventsUrl, {
                method: isEdit ? "PUT" : "POST",
                body: payload
            })
                .then(function (data) {
                    setStatus(data.message || "Saved successfully.", "success");
                    setEditing(null);
                    loadEvents();
                })
                .catch(function (error) {
                    var detail = error.payload && error.payload.errors
                        ? " " + Object.keys(error.payload.errors)
                            .map(function (key) { return error.payload.errors[key]; })
                            .join(" ")
                        : "";

                    setStatus(error.message + detail, "error");
                })
                .finally(function () {
                    submitBtn.disabled = false;
                });
        });

        cancelBtn.addEventListener("click", function () {
            setEditing(null);
        });

        tableBody.addEventListener("click", function (event) {
            var button = event.target.closest("button[data-action]");

            if (!button) {
                return;
            }

            var eventId = parseInt(button.getAttribute("data-id"), 10);

            if (button.getAttribute("data-action") === "edit") {
                apiRequest(config.eventsUrl, {
                    params: { id: eventId }
                })
                    .then(function (data) {
                        setEditing(data.data);
                        window.scrollTo({ top: 0, behavior: "smooth" });
                    })
                    .catch(function (error) {
                        setStatus(error.message, "error");
                    });
                return;
            }

            if (
                button.getAttribute("data-action") === "delete" &&
                window.confirm("Delete event #" + eventId + "? This cannot be undone.")
            ) {
                apiRequest(config.eventsUrl, {
                    method: "DELETE",
                    params: { id: eventId }
                })
                    .then(function (data) {
                        setStatus(data.message || "Deleted.", "success");
                        loadEvents();
                    })
                    .catch(function (error) {
                        setStatus(error.message, "error");
                    });
            }
        });

        if (searchInput) {
            searchInput.addEventListener(
                "input",
                debounce(function () {
                    state.search = searchInput.value.trim();
                    loadEvents();
                }, 350)
            );
        }

        if (statusInput) {
            statusInput.addEventListener("change", function () {
                state.status = statusInput.value;
                loadEvents();
            });
        }

        loadEvents();
    }

    document.addEventListener("DOMContentLoaded", function () {
        wireStudentSection();
        wireEventSection();
    });
})();
