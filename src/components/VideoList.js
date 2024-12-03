import React, { useState } from "react";
import { Button, ListGroup, Alert, ProgressBar, Spinner } from "react-bootstrap";

const VideoList = ({ videos, onBack, categoryName }) => {
    const [progress, setProgress] = useState(0);
    const [processingIndex, setProcessingIndex] = useState(null);
    const [fixedVideos, setFixedVideos] = useState([]);
    const [isFixing, setIsFixing] = useState(false);

    const handleFixVideos = async () => {
        setIsFixing(true);
        const totalVideos = videos.length;
        const processedVideos = [];

        for (let i = 0; i < totalVideos; i++) {
            setProcessingIndex(i); // Highlight the current video being processed
            const video = videos[i];

            // Call backend to fix the video
            const isSuccess = await fixVideoBackend(video);
            if (isSuccess) {
                processedVideos.push(video.id);
            }

            // Update progress
            setProgress(((i + 1) / totalVideos) * 100);
        }

        setFixedVideos(processedVideos);
        setProcessingIndex(null);
        setIsFixing(false);
    };

    // Call the backend to fix a video
    const fixVideoBackend = async (video) => {
        try {
            const response = await fetch("/wp-json/youtube-video-checker/v1/fix-video", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    postId: video.id,
                    videoId: video.video_id,
                    videoTitle: video.title,
                }),
            });

            const data = await response.json();
            if (data.success) {
                console.log(`Video ${video.id} fixed.`);
                return true;
            } else {
                console.error(data.message);
                return false;
            }
        } catch (error) {
            console.error("Error fixing video:", error);
            return false;
        }
    };

    return (
        <div className="p-4 shadow-lg bg-white rounded">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h5>Video List {categoryName}</h5>
                <Button
                    variant="danger"
                    onClick={handleFixVideos}
                    disabled={isFixing}
                >
                    {isFixing ? (
                        <>
                            <Spinner animation="border" size="sm" className="me-2" />
                            Fixing Videos...
                        </>
                    ) : (
                        "Fix Videos"
                    )}
                </Button>
            </div>

            {isFixing && (
                <ProgressBar now={progress} label={`${Math.round(progress)}%`} className="mb-3" />
            )}

            {videos.length > 0 ? (
                <ListGroup>
                    {videos.map((video, index) => (
                        <ListGroup.Item
                            key={index}
                            className={`small ${fixedVideos.includes(video.id) ? "text-success" : ""}`}
                        >
                            <a
                                href={`/wp-admin/post.php?post=${video.id}&action=edit`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-decoration-none"
                            >
                                {video.title} (ID: {video.id})
                            </a>
                            {processingIndex === index && (
                                <Spinner animation="grow" size="sm" className="ms-2 text-primary" />
                            )}
                            {fixedVideos.includes(video.id) && (
                                <span className="ms-2 text-success">✔ Fixed</span>
                            )}
                        </ListGroup.Item>
                    ))}
                </ListGroup>
            ) : (
                <Alert variant="warning">No videos found for this category.</Alert>
            )}

            <Button variant="secondary" className="mt-4" onClick={onBack}>
                Back to Categories
            </Button>
        </div>
    );
};

export default VideoList;
