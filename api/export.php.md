# API Endpoint: `export.php`

## Description

This script is responsible for exporting a presentation into a downloadable file. It currently supports HTML and plain text formats.

## Parameters

-   `presentation_id` (integer, required): The ID of the presentation to export.
-   `format` (string, required): The desired file format. Supported values are `html` and `txt`.

## Workflow

1.  **Input Validation:** The script first checks if both `presentation_id` and `format` are provided in the GET request. It also validates that the requested format is one of the allowed types.
2.  **Database Query:** It fetches the presentation's title, slides, and quizzes from the `presentations` table based on the provided ID.
3.  **Content Generation:** Based on the `format` parameter, it constructs the file content:
    *   **HTML (`html`):** Generates a complete HTML document, including the presentation title, slides, and quizzes.
    *   **Text (`txt`):** Generates a plain text version of the presentation, stripping all HTML tags from the content for readability.
4.  **HTTP Headers:** It sets the necessary HTTP headers (`Content-Type`, `Content-Disposition`, etc.) to instruct the browser to download the generated content as a file.
5.  **Output:** The script outputs the generated content, which the browser then handles as a file download.

## Status

-   [✅] Implemented basic export for HTML and TXT formats.

## Wish List / Future Improvements

-   [ ] Add support for PDF export.
-   [ ] Add support for DOC/DOCX export.
-   [ ] Add error handling for database connection failures.
