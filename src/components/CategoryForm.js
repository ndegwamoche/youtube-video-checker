import React, { useState, useEffect } from "react";
import { Form, Button, Alert, ProgressBar } from "react-bootstrap";

const CategoryForm = () => {
    const [categoryId, setCategoryId] = useState(""); // Store the selected category ID
    const [categories, setCategories] = useState([]); // Store the categories list
    const [errorMessage, setErrorMessage] = useState(""); // State to store the error message
    const [progress, setProgress] = useState(26); // Track progress (0-100)
    const [isProcessing, setIsProcessing] = useState(false); // To track if the process is running
    const [successMessage, setSuccessMessage] = useState(""); // Store success message

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
        setProgress(0);

        // Start checking videos and updating progress
        checkVideos(categoryId);
    };

    // Function to simulate the processing of posts and updating progress
    const checkVideos = (categoryId) => {
        // Start the process
        setIsProcessing(true);
        setSuccessMessage("Checking videos, please wait...");

        const fetchProgress = () => {
            fetch(yvcData.ajaxUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({
                    action: "get_progress", // Ensure this action is checking the server's progress
                    category_id: categoryId,
                }),
            })
                .then((response) => response.json()) // Expect JSON response
                .then((data) => {
                    const totalPosts = data.data.totalPosts;
                    const postsProcessed = data.data.postsProcessed;
                    const progress = data.data.progress;

                    // Update progress bar with the current progress
                    setProgress(progress);

                    // If all posts are processed, stop the process
                    if (postsProcessed === totalPosts) {
                        setSuccessMessage("All posts checked and updated successfully!");
                        setIsProcessing(false); // Stop processing
                    } else {
                        // If posts are still being processed, keep polling
                        setTimeout(fetchProgress, 1000); // Delay next fetch by 1 second
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    setSuccessMessage("Error processing posts.");
                    setIsProcessing(false);
                });
        };

        // Start the first call to initiate the process
        fetchProgress();
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

            {/* Progress Bar */}
            <div className="mb-3">
                <ProgressBar
                    animated
                    now={progress}
                    label={`${progress}%`}
                    className="mb-3"
                />
            </div>

            {/* Button */}
            <Button type="submit" variant="primary" className="w-100 mt-4" disabled={isProcessing}>
                {isProcessing ? "Processing..." : "Check Videos"}
            </Button>
        </Form>
    );
};

export default CategoryForm;
