using System;
using System.Web.Optimization;

namespace mastersband.App_Start
{
    public static class BundleConfig
    {
        public static void RegisterBundles(BundleCollection bundles)
        {
            bundles.Add(new StyleBundle("~/CSS/styles")
                .Include(
                    "~/CSS/normalize.css",
                    "~/CSS/site.css"
                ));

            bundles.Add(new ScriptBundle("~/scripts/app")
                .Include(
                    "~/Scripts/jquery-{version}.js"));
        }
    }
}