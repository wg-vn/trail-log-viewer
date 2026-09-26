# Recent Searches, Saved Searches & Syntax Tips

## Recent Searches

Every search query performed in Trail Log Viewer is recorded in the client browser's `localStorage` (up to 25 queries).

- Click the list icon (`≡`) on the right side of the search bar to view recent queries.
- Each item shows the search query and how long ago it was executed (`just now`, `5m ago`, `2d ago`).
- Hover over any query to click the **X** button to delete it, or click **Clear recent searches** to reset the list.

---

## Saved Searches

Bookmark complex queries for quick recall across sessions:

1. Enter your search query in the search bar.
2. Click the bookmark icon (`🔖`) inside the search bar.
3. In the modal, provide a descriptive name (e.g. `Payment Gateway 500s`).
4. Click **Save Search**.

Saved searches are accessible under the **SAVED** tab in the Searches popover. Clicking any saved search immediately populates the search bar and executes the query.

---

## Search Syntax & Cheat-Sheet

Click the question-mark icon (`?`) in the search bar to open the **Search Syntax Examples** reference card. Each example includes a **Use** button to immediately insert it into the search bar.

### Syntax Rules

| Pattern | Description | Example |
|---|---|---|
| **Simple Terms** | Matches entries containing all words | `stripe payment failed` |
| **Exact Phrases** | Enclose words in double quotes | `"SQLSTATE[23000]"` |
| **Negation (Exclusion)** | Prefix term with a minus sign `-` | `-CROND -worker` |
| **Boolean Grouping** | Combine conditions using `OR` and parentheses | `(postgres OR mysql) -failed` |
| **Regular Expressions** | Enclose pattern in slashes with optional flags | `/exception|fatal|error/i` |
| **CIDR & IP Addresses** | Filter specific IP or network range | `192.168.1.1` |
