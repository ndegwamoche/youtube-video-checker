import React from "react";
import { Container, Row, Col, Card } from "react-bootstrap";  // Import React-Bootstrap components
import CategoryForm from "./components/CategoryForm";  // Import your CategoryForm component

const App = () => {
    return (
        <Container>
            <Row className="justify-content-center">
                <Col md={8}>
                    <Card className="mt-4 p-4 shadow-sm">
                        <Card.Body>
                            <Card.Title className="mb-3">YouTube Video Checker Tool</Card.Title>
                            <Card.Text>
                                Select a category to check for missing YouTube videos in posts. This tool helps you identify
                                missing or deleted YouTubevideos and automatically add them to the respective posts.
                            </Card.Text>
                            <CategoryForm />
                        </Card.Body>
                    </Card>
                </Col>
            </Row>
        </Container>
    );
};

export default App;
