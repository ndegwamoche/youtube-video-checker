import React, { useState, useEffect } from "react";
import { Form, Button, Alert } from "react-bootstrap";
import VideoList from "./VideoList";

const CategoryForm = () => {
    const [categoryId, setCategoryId] = useState(""); // Store the selected category ID
    const [categoryName, setCategoryName] = useState(""); // Store the selected category name
    const [categories, setCategories] = useState([]); // Store the categories list
    const [errorMessage, setErrorMessage] = useState(""); // State to store the error message
    const [isProcessing, setIsProcessing] = useState(false); // To track if the process is running
    const [successMessage, setSuccessMessage] = useState(""); // Store success message
    const [videos, setVideos] = useState([]); // Store fetched videos
    const [showVideoList, setShowVideoList] = useState(false); // Toggle for showing video list
    const [searchTerm, setSearchTerm] = useState("");

    // Filter categories based on the search term
    const filteredCategories = categories.filter((category) =>
        category.name.toLowerCase().includes(searchTerm.toLowerCase())
    );

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

    // Update categoryName when categoryId or categories change
    useEffect(() => {
        if (categoryId) {
            const selectedCategory = categories.find((category) => category.id === categoryId);
            setCategoryName(selectedCategory ? selectedCategory.name : ""); // Set categoryName if category is found
        }
    }, [categoryId, categories]); // Added 'categories' as a dependency

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

        // Fetch videos for the selected category
        fetchVideos(categoryId);
    };

    // Function to fetch videos for the selected category
    const fetchVideos = (categoryId) => {
        setIsProcessing(true);
        setSuccessMessage("Fetching videos, please wait...");

        // Send POST request to the REST API endpoint
        fetch(yvcData.ajaxUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "yvc_get_posts",
                category_id: categoryId, // Category ID to fetch videos
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    setVideos(data.data.videos); // Store videos in state
                    setShowVideoList(true); // Show the video list screen
                    setSuccessMessage("");
                } else {
                    setErrorMessage("Error fetching videos: " + data.data.message); // Show error message
                }
                setIsProcessing(false);
            })
            .catch((error) => {
                console.error("Error:", error);
                setErrorMessage("Error fetching videos.");
                setSuccessMessage("");
                setIsProcessing(false);
            });
    };

    // Show the video list screen if toggled
    if (showVideoList) {
        return (
            <VideoList
                videos={videos}
                onBack={() => setShowVideoList(false)} // Go back to the category form
                categoryName={categoryName}
            />
        );
    }

    return (
        <Form onSubmit={handleSubmit} className="p-4 shadow-lg bg-white rounded">
            {/* Error Message */}
            {errorMessage && <Alert variant="danger" className="mb-3">{errorMessage}</Alert>}
            {/* Success Message */}
            {successMessage && <Alert variant="success" className="mb-3">{successMessage}</Alert>}

            {/* Filter Input */}
            <Form.Group controlId="categorySearch" className="mb-3">
                <Form.Control
                    type="text"
                    placeholder="Search Categories..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="mb-3 small"
                />
            </Form.Group>

            {/* Category Select Dropdown */}
            <Form.Group controlId="categorySelect" className="mb-3">
                <Form.Select
                    className="w-100"
                    value={categoryId}
                    onChange={(e) => setCategoryId(e.target.value)}
                >
                    <option value="">-- Choose a Category --</option>
                    {filteredCategories.length > 0 ? (
                        filteredCategories.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))
                    ) : (
                        <option value="">No categories found</option>
                    )}
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
