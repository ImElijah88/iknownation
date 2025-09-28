Version 1.0.0 - Autodenken MVP

This is the initial release of Autodenken, a web application designed to transform static content into dynamic, interactive, and gamified educational presentations.

Key Features:
- Initial Project Structure: Established the foundational directory and file structure.
- Dockerization: The application is containerized using Docker for both backend (Flask) and frontend (Next.js) services, ensuring portability and reproducibility.
- Basic Backend Setup: A Flask application is set up and configured to run within Docker, providing a basic API.
- Basic Frontend Setup: A Next.js application is set up and configured for Docker, providing the initial user interface.
- Presentation Generation Endpoint: A Flask API endpoint is available to generate presentation structures from text input.
- OpenRouter Integration: The backend is connected to OpenRouter to leverage AI for generating presentation content.
- Frontend Input & Presentation View: The user interface allows for text input and displays the generated presentation slides.
- Styling with Tailwind CSS: The frontend is styled with Tailwind CSS, providing a clean and modern design.
- Error Handling and Feedback: Robust error handling and user feedback mechanisms are implemented in the frontend.
- Unit & Integration Tests: Unit tests are written for the backend Flask application to ensure stability (manual execution required).