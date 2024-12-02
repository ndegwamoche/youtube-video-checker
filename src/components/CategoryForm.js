import React, { useState, useEffect } from "react";
import { Form, Button, Alert, ProgressBar } from "react-bootstrap";

const CategoryForm = () => {
    const [categoryId, setCategoryId] = useState(""); // Store the selected category ID
    const [categories, setCategories] = useState([]); // Store the categories list
    const [errorMessage, setErrorMessage] = useState(""); // State to store the error message
    const [isProcessing, setIsProcessing] = useState(false); // To track if the process is running
    const [successMessage, setSuccessMessage] = useState(""); // Store success message
    const [progress, setProgress] = useState(0); // Progress bar state

    // Fetch categories when the component is mounted
    useEffect(() => {
        fetch(yvcData.ajaxUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "yvc_get_categories", // This is the AJAX action we defined
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    setCategories(data.data); // Store categories in state
                } else {
                    console.error(data.data.message); // Handle error
                }
            })
            .catch((error) => {
                console.error("Error fetching categories:", error);
            });
    }, []); // Empty dependency array means this effect runs only once when the component mounts

    const handleSubmit = (e) => {
        e.preventDefault();

        // Check if a category is selected
        if (!categoryId) {
            setErrorMessage("Please select a category.");
            return;
        }

        // Reset error message if a category is selected
        setErrorMessage("");
        setIsProcessing(true); // Start the process

        // Initialize success message
        setSuccessMessage("");

        // Start checking videos
        checkVideos(categoryId);
    };

    // Function to check videos and submit posts
    const checkVideos = (categoryId) => {
        setIsProcessing(true);
        setSuccessMessage("Checking videos, please wait...");

        // Send POST request to the REST API endpoint
        fetch(yvcData.restUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                nonce: yvcData.noncex, // Nonce for security
                category_id: categoryId, // Category ID to be checked
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    setSuccessMessage(data.data.message); // Show success message from the response
                    setIsProcessing(false);
                } else {
                    setIsProcessing(false);
                    setSuccessMessage("");
                    setErrorMessage("Error processing posts: " + data.data.message); // Show error message from the response
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                setSuccessMessage("Error processing posts.");
                setIsProcessing(false);
            });
    };


    return (
        <Form onSubmit={handleSubmit} className="p-4 shadow-lg bg-white rounded">
            {/* Error Message */}
            {errorMessage && <Alert variant="danger" className="mb-3">{errorMessage}</Alert>}
            {/* Success Message */}
            {successMessage && <Alert variant="success" className="mb-3">{successMessage}</Alert>}

            <Form.Group controlId="categorySelect" className="mb-3">
                <Form.Select
                    className="w-100"
                    value={categoryId}
                    onChange={(e) => setCategoryId(e.target.value)}
                >
                    <option value="">-- Choose a Category --</option>
                    {categories.map((category) => (
                        <option key={category.id} value={category.id}>
                            {category.name}
                        </option>
                    ))}
                </Form.Select>
            </Form.Group>

            {/* Button */}
            <Button type="submit" variant="primary" className="w-100 mt-4" disabled={isProcessing}>
                {isProcessing ? "Processing..." : "Check Videos"}
            </Button>
        </Form>
    );
};

export default CategoryForm;
