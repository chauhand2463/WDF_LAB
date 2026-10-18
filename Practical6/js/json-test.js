


fetch("../data/student.json")

    .then(function (response) {

        if (!response.ok) {

            throw new Error("JSON file not found");

        }

        return response.json();

    })

    .then(function (students) {

        console.log("Student data loaded successfully");
        console.log(students);

        let loggedInID = localStorage.getItem("userID");

        if (!loggedInID) {

            loggedInID = "25CE001";

        }

        console.log("Logged-in Student ID:", loggedInID);

        const student = students.find(function (student) {

            return student.studentID === loggedInID;

        });

        if (student) {

            document.getElementById("studentName").textContent =
                student.name;

            document.getElementById("studentCourse").textContent =
                student.course;

            document.getElementById("studentUniversity").textContent =
                student.university;

            document.getElementById("studentSemester").textContent =
                student.semester;

            document.getElementById("studentEmail").textContent =
                student.email;

            document.getElementById("studentMobile").textContent =
                student.mobile;

        }

        else {

            document.getElementById("studentName").textContent =
                "Student not found";

            document.getElementById("studentCourse").textContent =
                "-";

            document.getElementById("studentUniversity").textContent =
                "-";

            document.getElementById("studentSemester").textContent =
                "-";

            document.getElementById("studentEmail").textContent =
                "-";

            document.getElementById("studentMobile").textContent =
                "-";

        }

    })

    .catch(function (error) {

        console.log("Error loading student data:", error);

    });