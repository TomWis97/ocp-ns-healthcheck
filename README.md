# OpenShift Namespace Healtcheck

Simple PHP script for checking all deployment configs in a namespace.

## Installation
1. Create a application within OpenShift: `oc new-app --name health-check <Repo name>`
2. Create a serviceaccount: `oc create sa health-check`
3. Grant permissions to serviceaccount: `oc policy -n $namespace view system:serviceaccount:$namespace:health-check`
4. Configure the DC of the application created in step 1 (GUI is recommended):
   - `NAMESPACE`: Set to namespace to use.
   - `TOKEN`: Set to secret of the SA's token.
   - `API_URL`: Set to OpenShift API URL.
5. Expose the application.
