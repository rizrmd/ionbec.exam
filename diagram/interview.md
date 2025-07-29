# Interview Feature

- [x] Can manage interview questions
- [x] Can manage interviews (from: exam)
- [ ] Can manage interview schedule (from: delivery)
- [ ] Can do live-scoring (from: scoring)

# State Documentation

```mermaid
---
title: Item->ItemType
---
stateDiagram
    [*] --> Simple
    Simple --> MultipleChoice
    Simple --> Essay
    Simple --> Interview
    MultipleChoice --> Vignette
    MultipleChoice --> NonVignette
    Essay --> Vignette
    Essay --> NonVignette
    Interview --> Vignette
```
