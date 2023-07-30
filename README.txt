# Issue Tracking System

Provides the block that list the latest 3 issues assigned to the current user.

## Installation
1. Add the module to the custom module directory web/modules/custom/issue_tracking_system
2. Once added Go to Extend page Administration->Extend and Search for Issue Tracking system
3. Check the checkbox and Install the module.

### Configuration
1. Once module enabled, Go to Structure -> Content type, Issue Content type gets created.
2. Add content for Issue Content type.
3. Go to Structure -> Taxonomy following taxonomies gets created, add the following terms.
   * Status (Term - To Do, In Progress, In Review, Done)
   * Priority (Term - Critical, High, Low, Trivial)
   * Issue type (Term - New feature, Change, Task, Bug, Improvement).
4. Go to Block layout Structure -> Block layout, move to the content section and click on Place block
   In the popup search for Issue Tracking System and place the block.
5. Block will be shown for Authenticated user having the issue assigned.
