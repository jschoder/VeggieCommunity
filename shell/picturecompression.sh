find ../pictures/74x74/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/100x100/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/200x200/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/w390/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/full-watermark/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80

find ../pictures/events/74x74/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/events/200x200/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/events/full-watermark/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80

find ../pictures/groups/74x74/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/groups/200x200/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/groups/full-watermark/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80

find ../pictures/real/full/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/real/full-watermark/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/small/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80

find ../pictures/thread/100x100/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/thread/full-watermark/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80
find ../pictures/thread/w390/ -maxdepth 1 -cmin 60 -iname '*.jpg' -print0 | xargs -0 jpegoptim -t -p --strip-all -m80