Plan: Export Endpoint
Objective: Create a PHP script that generates and serves a downloadable version of a presentation in various formats (HTML, PDF, DOC, TXT).

File to Create: export.php

Key Functionality:

Security First: The script must begin by including the session_manager.php file. If the user is not logged in, the script should stop immediately.

Input Handling: It will accept two GET parameters from the URL:

id: The presentation_key of the presentation to export.

format: The desired format (e.g., html, pdf, docx, txt).

Database Fetch:

The script will query the presentations table to get the title, slides (as JSON), and speech for the requested id.

Permission Check: It must verify that the presentation is either a default one (user_id IS NULL) or belongs to the currently logged-in user. If not, it should show an error.

Content Generation (based on format):

if ($format === 'txt'):

Create a simple text string containing the title and the full speech.

Set the appropriate headers for a text file download.

echo the text content.

if ($format === 'html'):

Generate a complete, self-contained HTML document.

Loop through the slides array and embed the slide HTML into the body.

Add basic CSS for a clean look.

Set the appropriate headers for an HTML file download.

echo the HTML content.

if ($format === 'pdf'):

Requires Library: This will use a free PHP library like TCPDF.

The script will instantiate a new PDF document.

It will loop through the slides data, adding each slide's content as a new page in the PDF.

Set the appropriate headers for a PDF file download.

Output the generated PDF.

if ($format === 'docx'):

Requires Library: This will use a free PHP library like PHPWord (from PHPOffice).

The script will create a new Word document object.

It will loop through the slides and add the content to the document.

Set the appropriate headers for a DOCX file download.

Save and output the generated document.

### Future Improvements:

*   **Implement `api/export.php`:** Develop the back-end script to generate downloadable files (HTML, PDF, DOC, TXT).
*   **Integrate Libraries:** Use appropriate PHP libraries for PDF (e.g., TCPDF, FPDF) and DOCX (e.g., PHPWord) generation.
*   **Front-end Integration:** Add export buttons to the `nownation.php` page (e.g., in the `#export-controls` section of the sidemenu) and link them to `api/export.php`.
