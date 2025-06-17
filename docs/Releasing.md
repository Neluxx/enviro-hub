# HOW TO RELEASE

## Prerequisites

- A local copy of the application
- All changes committed & pushed to the repository
- Access to the [repository](https://github.com/Neluxx/enviro-hub.git)

## The Release Process

- Update the [change log](../CHANGELOG.md)
- Push the changes to the repository
- [Draft a new release](https://github.com/Neluxx/enviro-hub/releases/new)
- Create a new tag to trigger the CI pipeline which creates the release zip
- Copy the [change log](../CHANGELOG.md) entries in raw as release notes
- You can find the release zip in the release artifacts once the pipeline has been successfully finished
- [Deploy the release](Deployment.md)
