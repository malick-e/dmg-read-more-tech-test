# DMG Read More (Gutenberg Block)

A lightweight WordPress plugin implementing the “Read More” block for the dmg::media technical test.

This block allows editors to search for and select a published post, and automatically outputs a stylized *Read More* link pointing to that post.

---

## Features

- **Search in InspectorControls**.
- Search by:
	- **Post title**
	- **Post ID**
- **Recent Articles** list appears by default when search is empty.
- **Pagination** controls (Previous / Next) for navigating results.
- **Live preview** shown in the block canvas.
- Output:

```html
<p class="dmg-read-more">
  Read More: <a href="https://example.com/the-post">The Post Title</a>
</p>
