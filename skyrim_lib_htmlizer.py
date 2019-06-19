#!/usr/bin/python
import sys
import os.path
from os.path import basename

parameter = ""

# Check if the parameter is one and only one
if len(sys.argv[1:]) != 1:
    print("Usage: skyrim_lib_htmlizer.py <file name>")
    sys.exit(2)

# Store the parameter in a variable
parameter = sys.argv[1:][0]

# If parameter is -h or --help print the usage
if parameter == "-h" or parameter == "--help":
    print("Usage: skyrim_lib_htmlizer.py <file name>")
    sys.exit(2)

# Check that the parameter is a valid file
if not os.path.isfile(parameter):
    print("File does not exist")
    sys.exit(2)

filename, file_extension = os.path.splitext(parameter)

# Check that the file is reasonable a txt file
if file_extension != ".txt":
    print("The script supports only txt files.")
    sys.exit(2)

# Strip the file name and cut off the extension
filename = basename(parameter)
filename = os.path.splitext(filename)[0]

# Create the output file name
outputfilename = filename + ".html"

# Opening the new file for Writing
file = open(parameter, 'r')
pagetitle = file.readlines()[0].rstrip()
# Starting writing file
f = open(outputfilename,"w")
f.write("<html xmlns=\"http://www.w3.org/1999/xhtml\">\n")
f.write("<head>\n")
f.write("\t<meta http-equiv='Content-type' content='text/html;charset=UTF-8'>\n")
f.write("\t<title>" + pagetitle + "</title>\n")
f.write("\t<link rel=\"stylesheet\" href=\"css/style.css\" type=\"text/css\" media=\"all\" />\n")
f.write("</head>\n")
f.write("<body>\n")

# Writing line by line
file = open(parameter, 'r')
for line in file:
    line = line.rstrip()
    if line == "":
        line = "&nbsp;"
    f.write("\t<p class=\"P1\">" + line + "</p>\n")

f.write("</body>\n")
f.write("</html>")
f.close()

print("<li>")
print("\t<a href=\"" + outputfilename + "\">")
print("\t\t" + pagetitle)
print("\t</a>")
print("</li>")
