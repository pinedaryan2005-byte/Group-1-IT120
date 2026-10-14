<?php

header("Content-Type: application/json");




$host = "localhost";

$db_user = "root";

$db_password = "";

$db_name = "sun_son_solar";




$conn = new mysqli(
    $host,
    $db_user,
    $db_password,
    $db_name
);


if ($conn->connect_error) {

    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);

    exit();
}




$data = json_decode(
    file_get_contents("php://input"),
    true
);


if (!$data) {

    echo json_encode([
        "status" => "error",
        "message" => "No data received"
    ]);

    exit();
}




$required = [

    "first_name",
    "last_name",
    "birthdate",
    "gender",
    "phone_number",
    "email",
    "address",
    "username",
    "password"

];




foreach ($required as $field) {

    if (
        !isset($data[$field]) ||
        trim($data[$field]) === ""
    ) {

        echo json_encode([

            "status" => "error",

            "message" =>
                "Please fill in all required fields."

        ]);

        $conn->close();

        exit();
    }
}




if (
    !filter_var(
        $data["email"],
        FILTER_VALIDATE_EMAIL
    )
) {

    echo json_encode([

        "status" => "error",

        "message" =>
            "Please enter a valid email address."

    ]);

    $conn->close();

    exit();
}




$hashed_password = password_hash(

    $data["password"],

    PASSWORD_DEFAULT

);




$stmt = $conn->prepare(

    "INSERT INTO users
    (
        firstName,
        middleName,
        lastName,
        birthdate,
        gender,
        telNo,
        email,
        address,
        username,
        password
    )

    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"

);


if (!$stmt) {

    echo json_encode([

        "status" => "error",

        "message" =>
            "Unable to prepare database request."

    ]);

    $conn->close();

    exit();
}




$stmt->bind_param(

    "ssssssssss",

    $data["first_name"],

    $data["middle_name"],

    $data["last_name"],

    $data["birthdate"],

    $data["gender"],

    $data["phone_number"],

    $data["email"],

    $data["address"],

    $data["username"],

    $hashed_password

);




if ($stmt->execute()) {

    echo json_encode([

        "status" => "success",

        "message" =>
            "User registered successfully!"

    ]);

}

else {

    if ($stmt->errno == 1062) {

        echo json_encode([

            "status" => "error",

            "message" =>
                "Username or email already exists."

        ]);

    }

    else {

        echo json_encode([

            "status" => "error",

            "message" =>
                "Registration failed: "
                . $stmt->error

        ]);

    }

}




$stmt->close();

$conn->close();

?>