using System;
using System.IO;
using System.Text;
using System.Web;
using System.Web.Mvc;
using Id3;

namespace mastersband.Helpers
{
    public static class HtmlHelpers
    {
        public static MvcHtmlString Playlist(this HtmlHelper html, string sourceVirtualPath)
        {
            var sourceAbsolutePath = VirtualPathUtility.ToAbsolute(sourceVirtualPath);

            var listItemBuilder = new StringBuilder();

            var sourcePhysicalPath = HttpContext.Current.Server.MapPath(sourceVirtualPath);
            var filePaths = Directory.EnumerateFiles(sourcePhysicalPath, "*.mp3");
            foreach (var filePath in filePaths)
            {
                var fileName = Path.GetFileName(filePath);
                var encodedFilename = HttpUtility.HtmlEncode(fileName);

                using (var mp3 = new Mp3File(filePath))
                {
                    var tag = mp3.GetTag(Id3TagFamily.FileStartTag);
                    
                    listItemBuilder.AppendFormat(
                        "<li><a href='{0}'>{1}</a></li>",
                        sourceAbsolutePath + '/' + encodedFilename,
                        tag.Title.Value);
                }
            }

            return new MvcHtmlString(listItemBuilder.ToString());
        }
    }
}