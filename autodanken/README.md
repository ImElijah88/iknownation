# Autodenken: AI-Powered Presentation Generator

## Project Description

Autodenken is a web application designed to transform static text content into dynamic, interactive, and gamified educational presentations. Leveraging AI, it automates the creation of engaging slides, making it easier for users to generate compelling educational material.

## Features

- **AI-Powered Content Generation:** Integrates with OpenRouter to generate presentation titles and slides from user-provided text.
- **Dockerized Environment:** The entire application stack (frontend and backend) is containerized using Docker and Docker Compose for easy setup, portability, and consistent environments.
- **Flask Backend:** A Python Flask API handles presentation generation logic and AI integration.
- **Next.js Frontend:** A modern React-based frontend built with Next.js provides a responsive and interactive user interface.
- **Tailwind CSS Styling:** The frontend is styled using Tailwind CSS for a clean, modern, and highly customizable design.
- **Basic Error Handling & Feedback:** Provides user feedback for presentation generation processes, including loading states, success messages, and error displays.
- **Unit Testing (Backend):** Includes unit tests for the Flask backend to ensure stability and correctness of API endpoints.

## Technology Stack

- **Frontend:** Next.js (React), JavaScript, Tailwind CSS
- **Backend:** Python, Flask, OpenAI (via OpenRouter)
- **Containerization:** Docker, Docker Compose

## Setup Instructions

To get Autodenken up and running on your local machine, follow these steps:

1.  **Clone the Repository:**
    ```bash
    git clone <repository_url>
    cd Autodenken
    ```

2.  **Environment Variables:**
    Create a `.env` file in the `autodanken/backend` directory with your OpenRouter API key:
    ```
    OPENROUTER_API_KEY=your_openrouter_api_key_here
    ```

3.  **Build and Run with Docker Compose:**
    Navigate to the `autodanken` directory and run:
    ```bash
    docker-compose build
    docker-compose up
    ```
    This will build the Docker images for both the frontend and backend, and start the services.

4.  **Access the Application:**
    -   **Frontend:** Open your browser and go to `http://localhost:3000`
    -   **Backend API:** The Flask backend will be running on `http://localhost:5000`

## Usage

1.  Navigate to the frontend application in your browser (`http://localhost:3000`).
2.  Enter the text you want to convert into a presentation in the provided text area.
3.  Click the "Generate Presentation" button.
4.  The application will display the generated presentation title and slides.

## Future Improvements

-   **Supabase Integration:** Implement user authentication (Google Login) and database storage for presentations using Supabase.
-   **User Dashboard:** Create a personalized dashboard for users to manage and view their saved presentations.
-   **Advanced Gamification:** Expand on XP and badges, potentially adding leaderboards or more complex gamified elements.
-   **Presentation Editing:** Allow users to edit generated slides and content.
-   **Export Options:** Provide various export formats for presentations (e.g., PDF, PowerPoint).
-   **Real-time Collaboration:** Implement features for multiple users to work on presentations simultaneously.
